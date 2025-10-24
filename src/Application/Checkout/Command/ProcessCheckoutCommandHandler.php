<?php

declare(strict_types=1);

namespace App\Application\Checkout\Command;

use App\Domain\Cart\Exception\CartNotFoundException;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Checkout\Aggregate\Order;
use App\Domain\Checkout\Exception\EmptyCartException;
use App\Domain\Checkout\Repository\OrderRepositoryInterface;
use App\Domain\Checkout\ValueObject\OrderId;
use App\Domain\Checkout\ValueObject\OrderItemId;
use App\Domain\Shared\Event\EventDispatcherInterface;

final readonly class ProcessCheckoutCommandHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private OrderRepositoryInterface $orderRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(ProcessCheckoutCommand $command): OrderId
    {
        $cartId = CartId::fromString($command->cartId);

        $cart = $this->cartRepository->findById($cartId);
        if (null === $cart) {
            throw CartNotFoundException::withId($cartId);
        }

        if ($cart->isEmpty()) {
            throw new EmptyCartException('Cannot checkout empty cart');
        }

        // Create order from cart
        $orderId = OrderId::generate();
        $order = Order::create($orderId, $cart->userId());

        // Transfer cart items to order
        foreach ($cart->items() as $cartItem) {
            $order->addItem(
                OrderItemId::generate(),
                $cartItem->productId(),
                $cartItem->name(),
                $cartItem->unitPrice(),
                $cartItem->quantity()
            );
        }

        // Calculate and capture total from items
        $order->captureTotal($order->calculateTotal());

        // Save order
        $this->orderRepository->save($order);

        // Dispatch order events
        $this->eventDispatcher->dispatch($order->pullDomainEvents());

        // Force cart expiration
        $cart->forceExpiration();

        // Save expired cart
        $this->cartRepository->save($cart);

        // Dispatch cart events
        $this->eventDispatcher->dispatch($cart->pullDomainEvents());

        return $orderId;
    }
}
