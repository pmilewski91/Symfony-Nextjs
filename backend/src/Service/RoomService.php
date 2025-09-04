<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Room;
use App\Exception\ValidationException;
use App\Exception\RoomNotFoundException;
use App\Exception\ReservationConflictException;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * [RoomService] Service class to handle business logic related to Room entity
 */
class RoomService
{
    public function __construct(
        private RoomRepository $roomRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Get list of rooms with optional filtering
     * 
     * @param Request $request HTTP request with optional query parameters
     * @return string JSON serialized rooms data
     */
    public function list(Request $request): string
    {
        // Check if we should filter only active rooms
        $activeOnly = $request->query->getBoolean('active_only', false);
        
        if ($activeOnly) {
            $rooms = $this->roomRepository->findActiveRooms();
        } else {
            $rooms = $this->roomRepository->findAll();
        }

        // Serialize the rooms data
        $data = $this->serializer->serialize($rooms, 'json', [
            'groups' => ['room:read']
        ]);

        return $data;
    }

    /**
     * Get a single room by ID
     * 
     * @param int $id Room ID
     * @return string JSON serialized room data
     * @throws RoomNotFoundException
     */
    public function getById(int $id): string
    {
        $room = $this->roomRepository->find($id);
        
        if (!$room) {
            throw new RoomNotFoundException($id);
        }
        
        // Serialize the room data
        return $this->serializer->serialize($room, 'json', [
            'groups' => ['room:read']
        ]);
    }

    /**
     * Create a new room
     *
     * @param Request $request
     * 
     * @return string
     * 
     */
    public function create(Request $request): string
    {
        // Get JSON data from request
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            $exception = new ValidationException('Invalid JSON data provided');
            $exception->addViolation('request', 'Invalid JSON data provided');
            throw $exception;
        }

        // Check if room with this name already exists
        if (isset($data['name'])) {
            $existingRoom = $this->roomRepository->findByName($data['name']);
            if ($existingRoom) {
                $exception = new ValidationException('Room with this name already exists');
                $exception->addViolation('name', 'Room with this name already exists');
                throw $exception;
            }
        }

        // Create new Room entity
        $room = new Room();
        
        // Set properties from request data
        if (isset($data['name'])) {
            $room->setName($data['name']);
        }
        
        if (isset($data['description'])) {
            $room->setDescription($data['description']);
        }
        
        // Set isActive - default to true if not provided
        $room->setIsActive($data['isActive'] ?? true);

        // Validate the entity
        $errors = $this->validator->validate($room);
        
        if (count($errors) > 0) {
            $exception = new ValidationException('Validation failed');
            
            foreach ($errors as $error) {
                $exception->addViolation($error->getPropertyPath(), $error->getMessage());
            }
            
            throw $exception;
        }

        // Save to database
        $this->entityManager->persist($room);
        $this->entityManager->flush();

        // Serialize and return the created room
        return $this->serializer->serialize($room, 'json', [
            'groups' => ['room:read']
        ]);
    }


    /**
     * Update an existing room
     *
     * @param int $id
     * @param Request $request
     * 
     * @return string
     * 
     */
    public function update(int $id, Request $request): string
    {
        // Find the room by ID
        $room = $this->roomRepository->find($id);

        if (!$room) {
            throw new RoomNotFoundException($id);
        }

        // Get JSON data from request
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            $exception = new ValidationException('Invalid JSON data provided');
            $exception->addViolation('request', 'Invalid JSON data provided');
            throw $exception;
        }

        // Check if room name is being changed and if new name already exists
        if (isset($data['name']) && $data['name'] !== $room->getName()) {
            $existingRoom = $this->roomRepository->findByName($data['name']);
            if ($existingRoom) {
                $exception = new ValidationException('Room with this name already exists');
                $exception->addViolation('name', 'Room with this name already exists');
                throw $exception;
            }
        }

        // Update properties from request data
        if (isset($data['name'])) {
            $room->setName($data['name']);
        }

        if (isset($data['description'])) {
            $room->setDescription($data['description']);
        }

        if (isset($data['isActive'])) {
            $room->setIsActive($data['isActive']);
        }

        // Validate the entity
        $errors = $this->validator->validate($room);

        if (count($errors) > 0) {
            $exception = new ValidationException('Validation failed');
            
            foreach ($errors as $error) {
                $exception->addViolation($error->getPropertyPath(), $error->getMessage());
            }
            
            throw $exception;
        }

        // Save changes to database
        $this->entityManager->flush();

        // Serialize and return the updated room
        return $this->serializer->serialize($room, 'json', [
            'groups' => ['room:read']
        ]);
    }

    /**
     * Delete a room
     *
     * @param int $id
     * 
     * @return Room
     * 
     */
    public function delete(int $id): Room
    {
        // Find the room by ID
        $room = $this->roomRepository->find($id);

        if (!$room) {
            throw new RoomNotFoundException($id);
        }

        // Check if room has any reservations
        $reservations = $room->getReservations();
        if (!$reservations->isEmpty()) {
            throw new ReservationConflictException($id, null, 'delete_conflict', null, $reservations->count());
        }

        // Remove the room from database
        $this->entityManager->remove($room);
        $this->entityManager->flush();

        return $room;
    }
}
