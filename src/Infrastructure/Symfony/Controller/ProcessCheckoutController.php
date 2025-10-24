<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Application\Checkout\Command\ProcessCheckoutCommand;
use App\Application\Checkout\Command\ProcessCheckoutCommandHandler;
use App\Domain\Checkout\Exception\EmptyCartException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/carts', name: 'api_carts_')]
#[OA\Tag(name: 'Checkout', description: 'Operaciones de pago y finalización de compra')]
final class ProcessCheckoutController extends AbstractController
{
    public function __construct(
        private readonly ProcessCheckoutCommandHandler $processCheckoutHandler,
    ) {
    }

    #[Route('/{cartId}/checkout', name: 'checkout', methods: ['POST'])]
    #[OA\Post(
        path: '/api/carts/{cartId}/checkout',
        summary: 'Procesar pago del carrito y crear orden',
        description: 'Procesa el pago de un carrito, creando una orden persistente con todos los artículos del carrito y marcando el carrito como expirado',
        parameters: [
            new OA\Parameter(
                name: 'cartId',
                description: 'Identificador único del carrito',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    format: 'uuid',
                    example: '987fcdeb-51a2-41d4-8901-23456789abcd'
                )
            ),
        ],
        tags: ['Checkout'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Pago procesado exitosamente, orden creada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'order_id',
                            type: 'string',
                            format: 'uuid',
                            description: 'Identificador único de la orden',
                            example: '123e4567-e89b-12d3-a456-426614174000'
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            description: 'Mensaje de éxito',
                            example: 'Orden creada exitosamente'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Solicitud inválida - El carrito está vacío o ID de carrito inválido',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'code', type: 'string', example: 'empty_cart'),
                                new OA\Property(property: 'message', type: 'string', example: 'No se puede procesar pago de carrito vacío'),
                            ]
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Carrito no encontrado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'code', type: 'string', example: 'cart_not_found'),
                                new OA\Property(property: 'message', type: 'string', example: 'Carrito no encontrado'),
                            ]
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function checkout(string $cartId): JsonResponse
    {
        try {
            $command = new ProcessCheckoutCommand($cartId);
            $orderId = $this->processCheckoutHandler->handle($command);

            return new JsonResponse(
                [
                    'order_id' => $orderId->value(),
                    'message' => 'Order created successfully',
                ],
                Response::HTTP_CREATED
            );
        } catch (EmptyCartException $e) {
            return new JsonResponse(
                [
                    'error' => [
                        'code' => 'empty_cart',
                        'message' => $e->getMessage(),
                    ],
                ],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\RuntimeException $e) {
            // Cart not found
            if (str_contains($e->getMessage(), 'Cart not found')) {
                return new JsonResponse(
                    [
                        'error' => [
                            'code' => 'cart_not_found',
                            'message' => 'Cart not found',
                        ],
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            return new JsonResponse(
                [
                    'error' => [
                        'code' => 'invalid_request',
                        'message' => $e->getMessage(),
                    ],
                ],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                [
                    'error' => [
                        'code' => 'invalid_cart_id',
                        'message' => $e->getMessage(),
                    ],
                ],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Throwable $e) {
            return new JsonResponse(
                [
                    'error' => [
                        'code' => 'internal_error',
                        'message' => 'Internal server error',
                    ],
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
