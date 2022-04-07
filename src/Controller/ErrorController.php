<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class ErrorController extends AbstractController
{
    public function show(\Throwable $exception, DebugLoggerInterface $logger = null): JsonResponse
    {
        if ($exception instanceof NotFoundHttpException) {
            return new JsonResponse($exception->getMessage(), $exception->getStatusCode());
        }
        return new JsonResponse($exception->getMessage(), 500);
    }
}
