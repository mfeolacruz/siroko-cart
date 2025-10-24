<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Application\Cart\Query\GetCartQuery;
use App\Application\Cart\Query\GetCartQueryHandler;
use App\Domain\Cart\Exception\CartNotFoundException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetCartController extends AbstractController
{
    public function __construct(
        private readonly GetCartQueryHandler $queryHandler,
    ) {
    }

    #[Route('/api/carts/{cartId}', name: 'get_cart', methods: ['GET'])]
    #[OA\Get(
        path: '/api/carts/{cartId}',
        summary: 'Obtener contenido del carrito de compras',
        description: 'Obtiene el contenido completo de un carrito de compras incluyendo todos los artículos y totales.',
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(
                name: 'cartId',
                description: 'ID del carrito en formato UUID v4',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    format: 'uuid',
                    example: '987fcdeb-51a2-41d4-8901-23456789abcd'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Contenido del carrito obtenido exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'cart_id',
                            type: 'string',
                            format: 'uuid',
                            description: 'Identificador del carrito',
                            example: '987fcdeb-51a2-41d4-8901-23456789abcd'
                        ),
                        new OA\Property(
                            property: 'total',
                            type: 'number',
                            format: 'float',
                            description: 'Monto total del carrito',
                            example: 199.98
                        ),
                        new OA\Property(
                            property: 'currency',
                            type: 'string',
                            description: 'Código de moneda',
                            example: 'EUR'
                        ),
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            description: 'Lista de artículos del carrito',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(
                                        property: 'id',
                                        type: 'string',
                                        format: 'uuid',
                                        description: 'Identificador del artículo del carrito',
                                        example: '123e4567-e89b-12d3-a456-426614174000'
                                    ),
                                    new OA\Property(
                                        property: 'product_id',
                                        type: 'string',
                                        format: 'uuid',
                                        description: 'Identificador del producto',
                                        example: '550e8400-e29b-41d4-a716-446655440001'
                                    ),
                                    new OA\Property(
                                        property: 'product_name',
                                        type: 'string',
                                        description: 'Nombre del producto',
                                        example: 'Siroko Cycling Glasses'
                                    ),
                                    new OA\Property(
                                        property: 'unit_price',
                                        type: 'number',
                                        format: 'float',
                                        description: 'Precio unitario',
                                        example: 99.99
                                    ),
                                    new OA\Property(
                                        property: 'currency',
                                        type: 'string',
                                        description: 'Código de moneda',
                                        example: 'EUR'
                                    ),
                                    new OA\Property(
                                        property: 'quantity',
                                        type: 'integer',
                                        description: 'Cantidad en el carrito',
                                        example: 2
                                    ),
                                    new OA\Property(
                                        property: 'subtotal',
                                        type: 'number',
                                        format: 'float',
                                        description: 'Subtotal para este artículo',
                                        example: 199.98
                                    ),
                                ],
                                type: 'object'
                            )
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Formato de ID de carrito inválido',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Formato UUID inválido'
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
                            type: 'string',
                            example: 'Carrito no encontrado'
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function __invoke(string $cartId): JsonResponse
    {
        try {
            $query = new GetCartQuery($cartId);
            $cart = $this->queryHandler->handle($query);

            // Build response with items
            $items = [];
            foreach ($cart->items() as $item) {
                $items[] = [
                    'id' => $item->id()->value(),
                    'product_id' => $item->productId()->value(),
                    'product_name' => $item->name()->value(),
                    'unit_price' => $item->unitPrice()->amount(),
                    'currency' => $item->unitPrice()->currency(),
                    'quantity' => $item->quantity()->value(),
                    'subtotal' => $item->subtotal()->amount(),
                ];
            }

            return new JsonResponse([
                'cart_id' => $cart->id()->value(),
                'total' => $cart->total()->amount(),
                'currency' => $cart->total()->currency(),
                'items' => $items,
            ], Response::HTTP_OK);
        } catch (CartNotFoundException $e) {
            return new JsonResponse(
                ['error' => 'Cart not found'],
                Response::HTTP_NOT_FOUND
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
