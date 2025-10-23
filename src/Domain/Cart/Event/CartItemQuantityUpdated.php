<?php

declare(strict_types=1);

namespace App\Domain\Cart\Event;

use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Cart\ValueObject\Quantity;
use App\Domain\Shared\Event\DomainEvent;

final readonly class CartItemQuantityUpdated extends DomainEvent
{
    private function __construct(
        private CartId $cartId,
        private CartItemId $cartItemId,
        private Quantity $previousQuantity,
        private Quantity $newQuantity,
        private \DateTimeImmutable $occurredOn,
    ) {
    }

    public static function create(
        CartId $cartId,
        CartItemId $cartItemId,
        Quantity $previousQuantity,
        Quantity $newQuantity,
        \DateTimeImmutable $occurredOn,
    ): self {
        return new self($cartId, $cartItemId, $previousQuantity, $newQuantity, $occurredOn);
    }

    public function cartId(): CartId
    {
        return $this->cartId;
    }

    public function cartItemId(): CartItemId
    {
        return $this->cartItemId;
    }

    public function previousQuantity(): Quantity
    {
        return $this->previousQuantity;
    }

    public function newQuantity(): Quantity
    {
        return $this->newQuantity;
    }

    public function eventName(): string
    {
        return 'cart.item_quantity_updated';
    }

    public function toPrimitives(): array
    {
        return [
            'cart_id' => $this->cartId->value(),
            'cart_item_id' => $this->cartItemId->value(),
            'previous_quantity' => $this->previousQuantity->value(),
            'new_quantity' => $this->newQuantity->value(),
        ];
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
