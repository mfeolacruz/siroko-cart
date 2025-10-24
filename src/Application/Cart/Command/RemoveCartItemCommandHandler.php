<?php

declare(strict_types=1);

namespace App\Application\Cart\Command;

use App\Domain\Cart\Exception\CartNotFoundException;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Shared\Event\EventDispatcherInterface;

final readonly class RemoveCartItemCommandHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(RemoveCartItemCommand $command): void
    {
        $cartId = CartId::fromString($command->cartId);

        $cart = $this->cartRepository->findById($cartId);

        if (null === $cart) {
            throw CartNotFoundException::withId($cartId);
        }

        $cartItemId = CartItemId::fromString($command->cartItemId);

        $cart->removeItem($cartItemId);

        $this->cartRepository->save($cart);

        $this->eventDispatcher->dispatch($cart->pullDomainEvents());
    }
}
