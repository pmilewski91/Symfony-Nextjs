<?php

namespace App\Controller;

use App\Exception\ValidationException;
use App\Exception\RoomNotFoundException;
use App\Exception\ReservationConflictException;
use App\Service\ReservationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/api/v1/reservations', name: 'api_reservations_')]
class ReservationController extends AbstractController
{
    public function __construct(
        private ReservationService $reservationService
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $data = $this->reservationService->list($request);

            return new JsonResponse(
                data: $data,
                status: 200
            );

        } catch (ValidationException $e) {
            return $this->json(['error' => 'Validation failed', 'details' => $e->getViolations()], 400);
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
            
            $data = $this->reservationService->create($request);
            return new JsonResponse(
                data: $data,
                status: 201
            );

        } catch (ValidationException $e) {
            return $this->json(['error' => 'Validation failed', 'details' => $e->getViolations()], 400);
        } catch (RoomNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (ReservationConflictException $e) {
            return $this->json($e->toArray(), 409);
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

            $response = $this->reservationService->listByRoom($roomId, $request);
            return new JsonResponse($response, 200);

        } catch (ValidationException $e) {
            return $this->json(['error' => 'Validation failed', 'details' => $e->getViolations()], 400);
        } catch (RoomNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to fetch reservations for room',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
