<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Application\Cart\Command\AddCartItemCommand;
use App\Application\Cart\Command\AddCartItemCommandHandler;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AddCartItemController extends AbstractController
{
    public function __construct(
        private readonly AddCartItemCommandHandler $commandHandler,
        private readonly CartRepositoryInterface $cartRepository,
    ) {
    }

    #[Route('/api/carts/{cartId}/items', name: 'add_cart_item', methods: ['POST'])]
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
