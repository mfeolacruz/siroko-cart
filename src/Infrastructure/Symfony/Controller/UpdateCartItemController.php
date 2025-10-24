<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Application\Cart\Command\UpdateCartItemQuantityCommand;
use App\Application\Cart\Command\UpdateCartItemQuantityCommandHandler;
use App\Domain\Cart\Exception\CartItemNotFoundException;
use App\Domain\Cart\Exception\CartNotFoundException;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateCartItemController extends AbstractController
{
    public function __construct(
        private readonly UpdateCartItemQuantityCommandHandler $commandHandler,
        private readonly CartRepositoryInterface $cartRepository,
    ) {
    }

    #[Route('/api/carts/{cartId}/items/{cartItemId}', name: 'update_cart_item', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/carts/{cartId}/items/{cartItemId}',
        summary: 'Actualizar cantidad de artículo del carrito',
        description: 'Actualiza la cantidad de un artículo específico en un carrito de compras.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'quantity',
                        type: 'integer',
                        minimum: 1,
                        description: 'New quantity for the cart item (minimum 1)',
                        example: 3
                    ),
                ],
                required: ['quantity'],
                type: 'object'
            )
        ),
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(
                name: 'cartId',
                description: 'Cart ID in UUID v4 format',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '550e8400-e29b-41d4-a716-446655440000'
            ),
            new OA\Parameter(
                name: 'cartItemId',
                description: 'Cart item ID in UUID v4 format',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '550e8400-e29b-41d4-a716-446655440001'
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart item quantity updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
                        new OA\Property(property: 'user_id', type: 'string', format: 'uuid', nullable: true, example: null),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2023-10-23T10:00:00+00:00'),
                        new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', example: '2023-10-30T10:00:00+00:00'),
                        new OA\Property(property: 'total_items', type: 'integer', example: 3),
                        new OA\Property(
                            property: 'total',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'amount_in_cents', type: 'integer', example: 8997),
                                new OA\Property(property: 'currency', type: 'string', example: 'EUR'),
                            ]
                        ),
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440001'),
                                    new OA\Property(property: 'product_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440002'),
                                    new OA\Property(property: 'name', type: 'string', example: 'Siroko Cycling Glasses'),
                                    new OA\Property(
                                        property: 'unit_price',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'amount_in_cents', type: 'integer', example: 2999),
                                            new OA\Property(property: 'currency', type: 'string', example: 'EUR'),
                                        ]
                                    ),
                                    new OA\Property(property: 'quantity', type: 'integer', example: 3),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2023-10-23T10:00:00+00:00'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2023-10-23T10:05:00+00:00'),
                                ]
                            )
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request - validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Invalid quantity: must be greater than 0'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Cart or cart item not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Cart not found with id: 550e8400-e29b-41d4-a716-446655440000'),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function __invoke(Request $request, string $cartId, string $cartItemId): JsonResponse
    {
        try {
            $content = $request->getContent();

            if (empty($content)) {
                return new JsonResponse(
                    ['error' => 'Request body cannot be empty'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $data = json_decode($content, true);

            if (!is_array($data)) {
                return new JsonResponse(
                    ['error' => 'Invalid JSON format'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            if (!array_key_exists('quantity', $data)) {
                return new JsonResponse(
                    ['error' => 'Missing required field: quantity'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            if (!is_int($data['quantity'])) {
                return new JsonResponse(
                    ['error' => 'Quantity must be an integer'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $command = new UpdateCartItemQuantityCommand(
                cartId: $cartId,
                cartItemId: $cartItemId,
                quantity: $data['quantity']
            );

            $this->commandHandler->handle($command);

            // Fetch updated cart
            $cart = $this->cartRepository->findById(CartId::fromString($cartId));

            if (null === $cart) {
                return new JsonResponse(
                    ['error' => 'Cart not found after updating item'],
                    Response::HTTP_NOT_FOUND
                );
            }

            // Build response following the same format as AddCartItemController
            $items = [];
            foreach ($cart->items() as $item) {
                $items[] = [
                    'id' => $item->id()->value(),
                    'product_id' => $item->productId()->value(),
                    'name' => $item->name()->value(),
                    'unit_price' => [
                        'amount_in_cents' => $item->unitPrice()->amountInCents(),
                        'currency' => $item->unitPrice()->currency(),
                    ],
                    'quantity' => $item->quantity()->value(),
                    'created_at' => $item->createdAt()->format(\DateTimeInterface::RFC3339),
                    'updated_at' => $item->updatedAt()->format(\DateTimeInterface::RFC3339),
                ];
            }

            return new JsonResponse([
                'id' => $cart->id()->value(),
                'user_id' => $cart->userId()?->value(),
                'created_at' => $cart->createdAt()->format(\DateTimeInterface::RFC3339),
                'expires_at' => $cart->expiresAt()->format(\DateTimeInterface::RFC3339),
                'total_items' => $cart->totalItems(),
                'total' => [
                    'amount_in_cents' => $cart->total()->amountInCents(),
                    'currency' => $cart->total()->currency(),
                ],
                'items' => $items,
            ], Response::HTTP_OK);
        } catch (CartNotFoundException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        } catch (CartItemNotFoundException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\JsonException) {
            return new JsonResponse(
                ['error' => 'Invalid JSON format'],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
