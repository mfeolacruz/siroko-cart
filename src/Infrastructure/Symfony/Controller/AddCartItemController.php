<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Application\Cart\Command\AddCartItemCommand;
use App\Application\Cart\Command\AddCartItemCommandHandler;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Cart', description: 'Shopping cart operations')]
final class AddCartItemController extends AbstractController
{
    public function __construct(
        private readonly AddCartItemCommandHandler $commandHandler,
        private readonly CartRepositoryInterface $cartRepository,
    ) {
    }

    #[Route('/api/carts/{cartId}/items', name: 'add_cart_item', methods: ['POST'])]
    #[OA\Post(
        path: '/api/carts/{cartId}/items',
        summary: 'Add item to shopping cart',
        description: 'Adds a new item to an existing shopping cart. If the same product already exists, the quantities will be combined.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'product_id',
                        type: 'string',
                        format: 'uuid',
                        description: 'Product ID in UUID v4 format',
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
                        description: 'Unit price of the product',
                        example: 99.99
                    ),
                    new OA\Property(
                        property: 'currency',
                        type: 'string',
                        description: 'Currency code (optional, defaults to EUR)',
                        example: 'EUR',
                        nullable: true
                    ),
                    new OA\Property(
                        property: 'quantity',
                        type: 'integer',
                        description: 'Quantity to add (must be positive)',
                        example: 2
                    ),
                ],
                required: ['product_id', 'product_name', 'unit_price', 'quantity'],
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
                description: 'Item added successfully to cart',
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
                            example: 199.98
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
                            description: 'List of cart items',
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
                description: 'Invalid request data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Missing required fields: product_id, product_name, unit_price, quantity'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Cart not found',
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
    public function __invoke(string $cartId, Request $request): JsonResponse
    {
        try {
            // Validate request body
            $content = $request->getContent();
            if ('' === $content) {
                return new JsonResponse(
                    ['error' => 'Failed to read request content'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $data = json_decode($content, true);

            if (!is_array($data) || !isset($data['product_id'], $data['product_name'], $data['unit_price'], $data['quantity'])) {
                return new JsonResponse(
                    ['error' => 'Missing required fields: product_id, product_name, unit_price, quantity'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Validate types
            if (!is_string($data['product_id']) || !is_string($data['product_name']) || !is_numeric($data['unit_price']) || !is_int($data['quantity'])) {
                return new JsonResponse(
                    ['error' => 'Invalid field types'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Validate quantity
            if ($data['quantity'] <= 0) {
                return new JsonResponse(
                    ['error' => 'Quantity must be a positive integer'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $currency = 'EUR';
            if (isset($data['currency']) && is_string($data['currency'])) {
                $currency = $data['currency'];
            }

            // Execute command
            $command = new AddCartItemCommand(
                $cartId,
                $data['product_id'],
                $data['product_name'],
                (float) $data['unit_price'],
                $currency,
                $data['quantity']
            );

            ($this->commandHandler)($command);

            // Fetch updated cart
            $cart = $this->cartRepository->findById(CartId::fromString($cartId));

            if (null === $cart) {
                return new JsonResponse(
                    ['error' => 'Cart not found after adding item'],
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
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Cart not found')) {
                return new JsonResponse(
                    ['error' => 'Cart not found'],
                    Response::HTTP_NOT_FOUND
                );
            }

            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
