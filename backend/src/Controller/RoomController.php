<?php

namespace App\Controller;

use App\Exception\ValidationException;
use App\Exception\RoomNotFoundException;
use App\Exception\ReservationConflictException;
use App\Service\RoomService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/api/v1/rooms', name: 'api_rooms_')]
class RoomController extends AbstractController
{
    public function __construct(
        private RoomService $roomService
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $data = $this->roomService->list($request);

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
            $data = $this->roomService->create($request);
            return new JsonResponse(
                data: json_decode($data, true),
                status: 201
            );
        } catch (ValidationException $e) {
            return $this->json(['error' => 'Validation failed', 'details' => $e->getViolations()], 400);
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
            $data = $this->roomService->update($id, $request);

            return new JsonResponse(
                data: json_decode($data, true),
                status: 200
            );
        } catch (RoomNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (ValidationException $e) {
            return $this->json(['error' => 'Validation failed', 'details' => $e->getViolations()], 400);
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
            $room = $this->roomService->delete($id);

            return new JsonResponse([
                'message' => 'Room deleted successfully',
                'deleted_room' => [
                    'id' => $room->getId(),
                    'name' => $room->getName()
                ]
            ], 200);
        } catch (RoomNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (ReservationConflictException $e) {
            return $this->json($e->toArray(), 409);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to delete room',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
