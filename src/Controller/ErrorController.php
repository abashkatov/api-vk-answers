<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class ErrorController extends AbstractController
{
    public function show(\Throwable $exception, DebugLoggerInterface $logger = null): JsonResponse
    {
        if ($exception instanceof NotFoundHttpException) {
            return new JsonResponse([
                'message' => 'Not found',
            ], $exception->getStatusCode());
        }
        if ($exception instanceof UnauthorizedHttpException) {
            return new JsonResponse([
                'message' => $exception->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse([
            'message' => $exception->getMessage(),
        ], 500);
    }
}
