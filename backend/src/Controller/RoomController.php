<?php

namespace App\Controller;

use App\Entity\Room;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/rooms', name: 'api_rooms_')]
class RoomController extends AbstractController
{
    public function __construct(
        private RoomRepository $roomRepository,
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
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

            return new JsonResponse(
                data: json_decode($data, true),
                status: 200
            );
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch rooms',
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

            // Check if room with this name already exists
            if (isset($data['name'])) {
                $existingRoom = $this->roomRepository->findByName($data['name']);
                if ($existingRoom) {
                    return new JsonResponse([
                        'error' => 'Room with this name already exists'
                    ], 409);
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
            $this->entityManager->persist($room);
            $this->entityManager->flush();

            // Serialize and return the created room
            $data = $this->serializer->serialize($room, 'json', [
                'groups' => ['room:read']
            ]);

            return new JsonResponse(
                data: json_decode($data, true),
                status: 201
            );

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to create room',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            // Find the room by ID
            $room = $this->roomRepository->find($id);
            
            if (!$room) {
                return new JsonResponse([
                    'error' => 'Room not found'
                ], 404);
            }

            // Get JSON data from request
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new JsonResponse([
                    'error' => 'Invalid JSON data provided'
                ], 400);
            }

            // Check if room name is being changed and if new name already exists
            if (isset($data['name']) && $data['name'] !== $room->getName()) {
                $existingRoom = $this->roomRepository->findByName($data['name']);
                if ($existingRoom) {
                    return new JsonResponse([
                        'error' => 'Room with this name already exists'
                    ], 409);
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

            // Save changes to database
            $this->entityManager->flush();

            // Serialize and return the updated room
            $data = $this->serializer->serialize($room, 'json', [
                'groups' => ['room:read']
            ]);

            return new JsonResponse(
                data: json_decode($data, true),
                status: 200
            );

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to update room',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            // Find the room by ID
            $room = $this->roomRepository->find($id);
            
            if (!$room) {
                return new JsonResponse([
                    'error' => 'Room not found'
                ], 404);
            }

            // Check if room has any reservations
            $reservations = $room->getReservations();
            if (!$reservations->isEmpty()) {
                return new JsonResponse([
                    'error' => 'Cannot delete room with existing reservations',
                    'reservations_count' => $reservations->count()
                ], 409);
            }

            // Remove the room from database
            $this->entityManager->remove($room);
            $this->entityManager->flush();

            return new JsonResponse([
                'message' => 'Room deleted successfully',
                'deleted_room' => [
                    'id' => $room->getId(),
                    'name' => $room->getName()
                ]
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to delete room',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
