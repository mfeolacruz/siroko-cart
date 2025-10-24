<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Application\Cart\Command\RemoveCartItemCommand;
use App\Application\Cart\Command\RemoveCartItemCommandHandler;
use App\Domain\Cart\Exception\CartItemNotFoundException;
use App\Domain\Cart\Exception\CartNotFoundException;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RemoveCartItemController extends AbstractController
{
    public function __construct(
        private readonly RemoveCartItemCommandHandler $commandHandler,
        private readonly CartRepositoryInterface $cartRepository,
    ) {
    }

    #[Route('/api/carts/{cartId}/items/{cartItemId}', name: 'remove_cart_item', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/carts/{cartId}/items/{cartItemId}',
        summary: 'Eliminar artículo del carrito de compras',
        description: 'Elimina un artículo de un carrito de compras existente.',
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(
                name: 'cartId',
                description: 'Cart ID in UUID v4 format',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    format: 'uuid',
                    example: '987fcdeb-51a2-41d4-8901-23456789abcd'
                )
            ),
            new OA\Parameter(
                name: 'cartItemId',
                description: 'Cart item ID in UUID v4 format',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    format: 'uuid',
                    example: '123e4567-e89b-12d3-a456-426614174000'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Item removed successfully from cart',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'cart_id',
                            type: 'string',
                            format: 'uuid',
                            description: 'Cart identifier',
                            example: '987fcdeb-51a2-41d4-8901-23456789abcd'
                        ),
                        new OA\Property(
                            property: 'total',
                            type: 'number',
                            format: 'float',
                            description: 'Total cart amount',
                            example: 99.99
                        ),
                        new OA\Property(
                            property: 'currency',
                            type: 'string',
                            description: 'Currency code',
                            example: 'EUR'
                        ),
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            description: 'List of remaining cart items',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(
                                        property: 'id',
                                        type: 'string',
                                        format: 'uuid',
                                        description: 'Cart item identifier',
                                        example: '123e4567-e89b-12d3-a456-426614174000'
                                    ),
                                    new OA\Property(
                                        property: 'product_id',
                                        type: 'string',
                                        format: 'uuid',
                                        description: 'Product identifier',
                                        example: '550e8400-e29b-41d4-a716-446655440001'
                                    ),
                                    new OA\Property(
                                        property: 'product_name',
                                        type: 'string',
                                        description: 'Product name',
                                        example: 'Siroko Cycling Glasses'
                                    ),
                                    new OA\Property(
                                        property: 'unit_price',
                                        type: 'number',
                                        format: 'float',
                                        description: 'Unit price',
                                        example: 99.99
                                    ),
                                    new OA\Property(
                                        property: 'currency',
                                        type: 'string',
                                        description: 'Currency code',
                                        example: 'EUR'
                                    ),
                                    new OA\Property(
                                        property: 'quantity',
                                        type: 'integer',
                                        description: 'Quantity in cart',
                                        example: 2
                                    ),
                                    new OA\Property(
                                        property: 'subtotal',
                                        type: 'number',
                                        format: 'float',
                                        description: 'Subtotal for this item',
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
                description: 'Invalid request parameters',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Invalid UUID format'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Cart or cart item not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Cart not found'
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function __invoke(string $cartId, string $cartItemId): JsonResponse
    {
        try {
            // Execute command
            $command = new RemoveCartItemCommand($cartId, $cartItemId);
            $this->commandHandler->handle($command);

            // Fetch updated cart
            $cart = $this->cartRepository->findById(CartId::fromString($cartId));

            if (null === $cart) {
                return new JsonResponse(
                    ['error' => 'Cart not found after removing item'],
                    Response::HTTP_NOT_FOUND
                );
            }

            // Build response
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
        } catch (CartItemNotFoundException $e) {
            return new JsonResponse(
                ['error' => 'Cart item not found'],
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
