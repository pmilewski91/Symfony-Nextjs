<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * [HealthController] Simple health check controller
 */
class HealthController
{
    /**
     * [HealthController] Simple health check controller
     *
     * @return JsonResponse
     * 
     */
    #[Route('/', name: 'health_check')]
    public function healthCheck(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'OK',
            'message' => 'Conference Room Booking API is running',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * [HealthController] API health check
     *
     * @return JsonResponse
     * 
     */
    #[Route('/api/health', name: 'api_health_check')]
    public function apiHealthCheck(): JsonResponse
    {
        return new JsonResponse([
            'api' => 'Conference Room Booking',
            'version' => '1.0.0',
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
