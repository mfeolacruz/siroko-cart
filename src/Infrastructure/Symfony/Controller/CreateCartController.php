<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Application\Cart\Command\CreateCartCommand;
use App\Application\Cart\Command\CreateCartCommandHandler;
use App\Domain\Shared\Exception\InvalidArgumentException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/carts', name: 'api_carts_')]
#[OA\Tag(name: 'Cart', description: 'Shopping cart operations')]
final class CreateCartController extends AbstractController
{
    public function __construct(
        private readonly CreateCartCommandHandler $createCartHandler,
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/carts',
        summary: 'Create a new shopping cart',
        description: 'Creates a new shopping cart, optionally associated with a user ID',
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'user_id',
                        type: 'string',
                        format: 'uuid',
                        description: 'Optional user ID in UUID v4 format',
                        example: '550e8400-e29b-41d4-a716-446655440000', // UUID v4 válido
                        nullable: true
                    ),
                ],
                type: 'object'
            )
        ),
        tags: ['Cart'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Cart created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'id',
                            type: 'string',
                            format: 'uuid',
                            description: 'Unique cart identifier',
                            example: '987fcdeb-51a2-41d4-8901-23456789abcd' // UUID v4 válido
                        ),
                        new OA\Property(
                            property: 'user_id',
                            type: 'string',
                            format: 'uuid',
                            description: 'User ID if provided',
                            example: '550e8400-e29b-41d4-a716-446655440000', // UUID v4 válido
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'created_at',
                            type: 'string',
                            format: 'date-time',
                            description: 'Cart creation timestamp',
                            example: '2025-10-23T00:43:05+00:00'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request data (malformed JSON or invalid UUID v4 format)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'code', type: 'string', example: 'invalid_uuid'),
                                new OA\Property(property: 'message', type: 'string', example: 'Invalid UUID v4 format'),
                            ]
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        try {
            $content = $request->getContent();
            $data = json_decode($content, true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                return new JsonResponse(
                    ['error' => 'Invalid JSON format'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            if (!is_array($data)) {
                $data = [];
            }

            $userId = isset($data['user_id']) && is_string($data['user_id'])
                ? $data['user_id']
                : null;

            $command = new CreateCartCommand($userId);
            $cartId = $this->createCartHandler->handle($command);

            return new JsonResponse(
                [
                    'id' => $cartId->value(),
                    'user_id' => $userId,
                    'created_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
                ],
                Response::HTTP_CREATED
            );
        } catch (\InvalidArgumentException $e) {
            // Captura tanto InvalidArgumentException como las excepciones del dominio
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Throwable $e) {
            // Cualquier otra excepción inesperada
            return new JsonResponse(
                ['error' => 'Internal server error'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
