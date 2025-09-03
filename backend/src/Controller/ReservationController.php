<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/reservations', name: 'api_reservations_')]
class ReservationController extends AbstractController
{
    public function __construct(
        private ReservationRepository $reservationRepository,
        private SerializerInterface $serializer,
        private RoomRepository $roomRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
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
                    return new JsonResponse([
                        'error' => 'Invalid date_from format. Use Y-m-d H:i:s or Y-m-d'
                    ], 400);
                }
            }

            if ($dateTo) {
                try {
                    $dateToObj = new \DateTime($dateTo);
                    $queryBuilder->andWhere('r.endDateTime <= :dateTo')
                        ->setParameter('dateTo', $dateToObj);
                } catch (\Exception $e) {
                    return new JsonResponse([
                        'error' => 'Invalid date_to format. Use Y-m-d H:i:s or Y-m-d'
                    ], 400);
                }
            }

            // Order by start date
            $queryBuilder->orderBy('r.startDateTime', 'ASC');

            $reservations = $queryBuilder->getQuery()->getResult();

            // Serialize the reservations data
            $data = $this->serializer->serialize($reservations, 'json', [
                'groups' => ['reservation:read', 'room:read']
            ]);

            return new JsonResponse(
                data: json_decode($data, true),
                status: 200
            );

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch reservations',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            // Get JSON data from request
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new JsonResponse([
                    'error' => 'Invalid JSON data provided'
                ], 400);
            }

            // Validate required fields
            $requiredFields = ['room_id', 'reserved_by', 'start_date_time', 'end_date_time'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return new JsonResponse([
                        'error' => "Field '{$field}' is required"
                    ], 400);
                }
            }

            // Find the room
            $room = $this->roomRepository->find($data['room_id']);
            if (!$room) {
                return new JsonResponse([
                    'error' => 'Room not found'
                ], 404);
            }

            // Check if room is active
            if (!$room->isActive()) {
                return new JsonResponse([
                    'error' => 'Cannot make reservation for inactive room'
                ], 409);
            }

            // Parse dates
            try {
                $startDateTime = new \DateTime($data['start_date_time']);
                $endDateTime = new \DateTime($data['end_date_time']);
            } catch (\Exception $e) {
                return new JsonResponse([
                    'error' => 'Invalid date format. Use Y-m-d H:i:s format'
                ], 400);
            }

            // Check for conflicting reservations
            $conflictingReservations = $this->reservationRepository->findConflictingReservations(
                $room->getId(),
                $startDateTime,
                $endDateTime
            );

            if (!empty($conflictingReservations)) {
                return new JsonResponse([
                    'error' => 'Time slot conflicts with existing reservation',
                    'conflicting_reservations' => array_map(function($reservation) {
                        return [
                            'id' => $reservation->getId(),
                            'reserved_by' => $reservation->getReservedBy(),
                            'start_date_time' => $reservation->getStartDateTime()->format('Y-m-d H:i:s'),
                            'end_date_time' => $reservation->getEndDateTime()->format('Y-m-d H:i:s')
                        ];
                    }, $conflictingReservations)
                ], 409);
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
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = [
                        'field' => $error->getPropertyPath(),
                        'message' => $error->getMessage()
                    ];
                }
                
                return new JsonResponse([
                    'error' => 'Validation failed',
                    'violations' => $errorMessages
                ], 400);
            }

            // Save to database
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();

            // Serialize and return the created reservation
            $data = $this->serializer->serialize($reservation, 'json', [
                'groups' => ['reservation:read', 'room:read']
            ]);

            return new JsonResponse(
                data: json_decode($data, true),
                status: 201
            );

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to create reservation',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/room/{roomId}', name: 'list_by_room', methods: ['GET'])]
    public function listByRoom(int $roomId, Request $request): JsonResponse
    {
        try {
            // Check if room exists
            $room = $this->roomRepository->find($roomId);
            if (!$room) {
                return new JsonResponse([
                    'error' => 'Room not found'
                ], 404);
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
                    return new JsonResponse([
                        'error' => 'Invalid date_from format. Use Y-m-d H:i:s or Y-m-d'
                    ], 400);
                }
            }

            if ($dateTo) {
                try {
                    $dateToObj = new \DateTime($dateTo);
                    $queryBuilder->andWhere('r.endDateTime <= :dateTo')
                        ->setParameter('dateTo', $dateToObj);
                } catch (\Exception $e) {
                    return new JsonResponse([
                        'error' => 'Invalid date_to format. Use Y-m-d H:i:s or Y-m-d'
                    ], 400);
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
            $response = [
                'room' => [
                    'id' => $room->getId(),
                    'name' => $room->getName(),
                    'description' => $room->getDescription(),
                    'isActive' => $room->isActive()
                ],
                'reservations' => json_decode($data, true),
                'total_reservations' => count($reservations)
            ];

            return new JsonResponse($response, 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch reservations for room',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
