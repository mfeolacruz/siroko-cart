<?php

declare(strict_types=1);

namespace App\Application\Cart\Command;

use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\Money;
use App\Domain\Cart\ValueObject\ProductId;
use App\Domain\Cart\ValueObject\ProductName;
use App\Domain\Cart\ValueObject\Quantity;
use App\Domain\Shared\Event\EventDispatcherInterface;

final readonly class AddCartItemCommandHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(AddCartItemCommand $command): void
    {
        $cartId = CartId::fromString($command->cartId);

        $cart = $this->cartRepository->findById($cartId);

        if (null === $cart) {
            throw new \RuntimeException('Cart not found');
        }

        $productId = ProductId::fromString($command->productId);
        $productName = ProductName::fromString($command->productName);
        $unitPrice = Money::fromFloat($command->price, $command->currency);
        $quantity = Quantity::fromInt($command->quantity);

        $cart->addItem($productId, $productName, $unitPrice, $quantity);

        $this->cartRepository->save($cart);

        $this->eventDispatcher->dispatch($cart->pullDomainEvents());
    }
}
