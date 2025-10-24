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
#[OA\Tag(name: 'Cart', description: 'Operaciones del carrito de compras')]
final class CreateCartController extends AbstractController
{
    public function __construct(
        private readonly CreateCartCommandHandler $createCartHandler,
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/carts',
        summary: 'Crear un nuevo carrito de compras',
        description: 'Crea un nuevo carrito de compras, opcionalmente asociado con un ID de usuario',
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'user_id',
                        type: 'string',
                        format: 'uuid',
                        description: 'ID de usuario opcional en formato UUID v4',
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
                description: 'Carrito creado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'id',
                            type: 'string',
                            format: 'uuid',
                            description: 'Identificador único del carrito',
                            example: '987fcdeb-51a2-41d4-8901-23456789abcd' // UUID v4 válido
                        ),
                        new OA\Property(
                            property: 'user_id',
                            type: 'string',
                            format: 'uuid',
                            description: 'ID de usuario (si se proporciona)',
                            example: '550e8400-e29b-41d4-a716-446655440000', // UUID v4 válido
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'created_at',
                            type: 'string',
                            format: 'date-time',
                            description: 'Momento de creación del carrito',
                            example: '2025-10-23T00:43:05+00:00'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Datos de solicitud inválidos (JSON mal formado o formato UUID v4 inválido)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'code', type: 'string', example: 'invalid_uuid'),
                                new OA\Property(property: 'message', type: 'string', example: 'Formato UUID v4 inválido'),
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
