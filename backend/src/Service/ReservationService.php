<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Exception\ReservationConflictException;
use App\Exception\RoomNotFoundException;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use App\Exception\ValidationException;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * [ReservationService] Service class to handle business logic related to Reservation entity
 */
class ReservationService
{
    public function __construct(
        private ReservationRepository $reservationRepository,
        private SerializerInterface $serializer,
        private RoomRepository $roomRepository,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private NotificationService $notificationService
    ) {
    }

    /**
     * Get filtered list of reservations
     * 
     * @param Request $request
     * @return array Array of reservation data
     * @throws ValidationException
     */
    public function list(Request $request): array
    {
        // Get query parameters for filtering
        $roomId = $request->query->get('room_id');
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');
        
        // Start with basic query
        $queryBuilder = $this->reservationRepository->createQueryBuilder('r')
            ->leftJoin('r.room', 'room')
            ->addSelect('room');

        // Filter by room if specified
        if ($roomId) {
            $queryBuilder->andWhere('r.room = :roomId')
                ->setParameter('roomId', $roomId);
        }

        // Filter by date range if specified
        if ($dateFrom) {
            try {
                $dateFromObj = new \DateTime($dateFrom);
                $queryBuilder->andWhere('r.startDateTime >= :dateFrom')
                    ->setParameter('dateFrom', $dateFromObj);
            } catch (\Exception $e) {
                $exception = new ValidationException('Invalid date_from format. Use Y-m-d H:i:s or Y-m-d');
                $exception->addViolation('date_from', 'Invalid date_from format. Use Y-m-d H:i:s or Y-m-d');
                throw $exception;
            }
        }

        if ($dateTo) {
            try {
                $dateToObj = new \DateTime($dateTo);
                $queryBuilder->andWhere('r.endDateTime <= :dateTo')
                    ->setParameter('dateTo', $dateToObj);
            } catch (\Exception $e) {
                $exception = new ValidationException('Invalid date_to format. Use Y-m-d H:i:s or Y-m-d');
                $exception->addViolation('date_to', 'Invalid date_to format. Use Y-m-d H:i:s or Y-m-d');
                throw $exception;
            }
        }

        // Order by start date
        $queryBuilder->orderBy('r.startDateTime', 'ASC');

        $reservations = $queryBuilder->getQuery()->getResult();

        // Serialize the reservations data and return as array
        $serializedData = $this->serializer->serialize($reservations, 'json', [
            'groups' => ['reservation:read', 'room:read']
        ]);

        return json_decode($serializedData, true);
    }

    /**
     * Create a new reservation
     *
     * @param Request $request
     * 
     * @return array
     * 
     */
    public function create(Request $request):array
    {
        // Get JSON data from request
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                $exception = new ValidationException('Invalid JSON data provided');
                $exception->addViolation('request', 'Invalid JSON data provided');
                throw $exception;
            }

            // Validate required fields
            $requiredFields = ['room_id', 'reserved_by', 'start_date_time', 'end_date_time'];
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                $exception = new ValidationException('Required fields are missing');
                foreach ($missingFields as $field) {
                    $exception->addViolation($field, "Field '{$field}' is required");
                }
                throw $exception;
            }

            // Find the room
            $room = $this->roomRepository->find($data['room_id']);
            if (!$room) {
                throw new RoomNotFoundException((int) $data['room_id']);
            }

            // Check if room is active
            if (!$room->isActive()) {
                throw new ReservationConflictException(
                    (int) $data['room_id'], 
                    'Cannot make reservation for inactive room', 
                    'inactive_room'
                );
            }

            // Parse dates
            try {
                $startDateTime = new \DateTime($data['start_date_time']);
                $endDateTime = new \DateTime($data['end_date_time']);
            } catch (\Exception $e) {
                $exception = new ValidationException('Invalid date format. Use Y-m-d H:i:s format');
                $exception->addViolation('start_date_time', 'Invalid date format. Use Y-m-d H:i:s format');
                $exception->addViolation('end_date_time', 'Invalid date format. Use Y-m-d H:i:s format');
                throw $exception;
            }

            // Check for conflicting reservations
            $conflictingReservations = $this->reservationRepository->findConflictingReservations(
                $room->getId(),
                $startDateTime,
                $endDateTime
            );

            if (!empty($conflictingReservations)) {
                $conflictData = array_map(function($reservation) {
                    return [
                        'id' => $reservation->getId(),
                        'reserved_by' => $reservation->getReservedBy(),
                        'start_date_time' => $reservation->getStartDateTime()->format('Y-m-d H:i:s'),
                        'end_date_time' => $reservation->getEndDateTime()->format('Y-m-d H:i:s')
                    ];
                }, $conflictingReservations);

                throw new ReservationConflictException(
                    $room->getId(), 
                    'Time slot conflicts with existing reservation', 
                    'time_conflict', 
                    $conflictData
                );
            }

            // Create new Reservation entity
            $reservation = new Reservation();
            $reservation->setRoom($room);
            $reservation->setReservedBy($data['reserved_by']);
            $reservation->setStartDateTime($startDateTime);
            $reservation->setEndDateTime($endDateTime);

            // Set optional email
            if (isset($data['reserved_by_email'])) {
                $reservation->setReservedByEmail($data['reserved_by_email']);
            }

            // Validate the entity
            $errors = $this->validator->validate($reservation);
            
            if (count($errors) > 0) {
                // $errorMessages = [];
                $exception = new ValidationException('Validation failed');
                foreach ($errors as $error) {
                    $exception->addViolation($error->getPropertyPath(), $error->getMessage());
                }
                
                throw $exception;
            }

            // Save to database
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();

            // Send notification about new reservation to RabbitMQ
            $this->notificationService->sendReservationCreatedNotification($reservation);

            // Serialize and return the created reservation
            $data = $this->serializer->serialize($reservation, 'json', [
                'groups' => ['reservation:read', 'room:read']
            ]);

            return json_decode($data, true);
    }

    /**
     * Create a new reservation
     *
     * @param int $roomId
     * @param Request $request
     * 
     * @return array
     * 
     */
    public function listByRoom(int $roomId, Request $request): array
    {
        // Check if room exists
            $room = $this->roomRepository->find($roomId);
            if (!$room) {
                throw new RoomNotFoundException($roomId);
            }

            // Get query parameters for additional filtering
            $dateFrom = $request->query->get('date_from');
            $dateTo = $request->query->get('date_to');
            $includeRoomDetails = $request->query->getBoolean('include_room_details', true);

            // Start with basic query for specific room
            $queryBuilder = $this->reservationRepository->createQueryBuilder('r')
                ->andWhere('r.room = :roomId')
                ->setParameter('roomId', $roomId);

            // Add room details if requested
            if ($includeRoomDetails) {
                $queryBuilder->leftJoin('r.room', 'room')
                    ->addSelect('room');
            }

            // Filter by date range if specified
            if ($dateFrom) {
                try {
                    $dateFromObj = new \DateTime($dateFrom);
                    $queryBuilder->andWhere('r.startDateTime >= :dateFrom')
                        ->setParameter('dateFrom', $dateFromObj);
                } catch (\Exception $e) {
                    $exception = new ValidationException('Invalid date_from format. Use Y-m-d H:i:s or Y-m-d');
                    $exception->addViolation('date_from', 'Invalid date_from format. Use Y-m-d H:i:s or Y-m-d');
                    throw $exception;
                }
            }

            if ($dateTo) {
                try {
                    $dateToObj = new \DateTime($dateTo);
                    $queryBuilder->andWhere('r.endDateTime <= :dateTo')
                        ->setParameter('dateTo', $dateToObj);
                } catch (\Exception $e) {
                    $exception = new ValidationException('Invalid date_to format. Use Y-m-d H:i:s or Y-m-d');
                    $exception->addViolation('date_to', 'Invalid date_to format. Use Y-m-d H:i:s or Y-m-d');
                    throw $exception;
                }
            }

            // Order by start date
            $queryBuilder->orderBy('r.startDateTime', 'ASC');

            $reservations = $queryBuilder->getQuery()->getResult();

            // Prepare serialization groups
            $groups = ['reservation:read'];
            if ($includeRoomDetails) {
                $groups[] = 'room:read';
            }

            // Serialize the reservations data
            $data = $this->serializer->serialize($reservations, 'json', [
                'groups' => $groups
            ]);

            // Prepare response with room information
            return [
                'room' => [
                    'id' => $room->getId(),
                    'name' => $room->getName(),
                    'description' => $room->getDescription(),
                    'isActive' => $room->isActive()
                ],
                'reservations' => json_decode($data, true),
                'total_reservations' => count($reservations)
            ];
    }
}
