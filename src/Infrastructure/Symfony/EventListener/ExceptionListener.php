<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final readonly class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof \InvalidArgumentException) {
            // Extraer mensaje limpio
            $message = $this->cleanDomainExceptionMessage($exception->getMessage());

            $response = new JsonResponse(
                [
                    'error' => [
                        'code' => 'invalid_argument',
                        'message' => $message,
                    ],
                ],
                Response::HTTP_BAD_REQUEST
            );

            $event->setResponse($response);
        }
    }

    private function cleanDomainExceptionMessage(string $message): string
    {
        // Si el mensaje es del tipo "<Class> does not allow the value <value>."
        // Lo convertimos a algo más amigable
        if (preg_match('/<.*?> does not allow the value <.*?>\./', $message)) {
            return 'Invalid UUID v4 format';
        }

        return $message;
    }
}
