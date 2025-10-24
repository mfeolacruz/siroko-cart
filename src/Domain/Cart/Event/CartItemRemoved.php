<?php

declare(strict_types=1);

namespace App\Domain\Cart\Event;

use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Shared\Event\DomainEvent;
use App\Domain\Shared\ValueObject\ProductId;

final readonly class CartItemRemoved extends DomainEvent
{
    public function __construct(
        private CartId $cartId,
        private CartItemId $cartItemId,
        private ProductId $productId,
    ) {
        parent::__construct();
    }

    public static function create(
        CartId $cartId,
        CartItemId $cartItemId,
        ProductId $productId,
    ): self {
        return new self($cartId, $cartItemId, $productId);
    }

    public function cartId(): CartId
    {
        return $this->cartId;
    }

    public function cartItemId(): CartItemId
    {
        return $this->cartItemId;
    }

    public function productId(): ProductId
    {
        return $this->productId;
    }

    public function eventName(): string
    {
        return 'cart.item.removed';
    }

    public function toPrimitives(): array
    {
        return [
            'cart_id' => $this->cartId->value(),
            'cart_item_id' => $this->cartItemId->value(),
            'product_id' => $this->productId->value(),
            'occurred_on' => $this->occurredOn()->format(\DateTimeInterface::RFC3339),
        ];
    }
}
