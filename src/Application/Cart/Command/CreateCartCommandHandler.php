<?php

declare(strict_types=1);

namespace App\Application\Cart\Command;

use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\UserId;
use App\Domain\Shared\Event\EventDispatcherInterface;

final readonly class CreateCartCommandHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(CreateCartCommand $command): CartId
    {
        $cartId = CartId::generate();
        $userId = null !== $command->userId
            ? UserId::fromString($command->userId)
            : null;

        $cart = Cart::create($cartId, $userId);

        $this->cartRepository->save($cart);

        $this->eventDispatcher->dispatch($cart->pullDomainEvents());

        return $cartId;
    }
}
