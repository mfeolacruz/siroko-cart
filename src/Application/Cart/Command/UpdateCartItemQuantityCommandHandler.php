<?php

declare(strict_types=1);

namespace App\Application\Cart\Command;

use App\Domain\Cart\Exception\CartNotFoundException;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Cart\ValueObject\Quantity;
use App\Domain\Shared\Event\EventDispatcherInterface;

final readonly class UpdateCartItemQuantityCommandHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(UpdateCartItemQuantityCommand $command): void
    {
        $cartId = CartId::fromString($command->cartId);

        $cart = $this->cartRepository->findById($cartId);

        if (null === $cart) {
            throw CartNotFoundException::withId($cartId);
        }

        $cartItemId = CartItemId::fromString($command->cartItemId);
        $quantity = Quantity::fromInt($command->quantity);

        $cart->updateItemQuantity($cartItemId, $quantity);

        $this->cartRepository->save($cart);

        $this->eventDispatcher->dispatch($cart->pullDomainEvents());
    }
}
