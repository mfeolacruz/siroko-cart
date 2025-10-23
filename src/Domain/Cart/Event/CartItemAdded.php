<?php

declare(strict_types=1);

namespace App\Domain\Cart\Event;

use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Cart\ValueObject\Money;
use App\Domain\Cart\ValueObject\ProductId;
use App\Domain\Cart\ValueObject\ProductName;
use App\Domain\Cart\ValueObject\Quantity;
use App\Domain\Shared\Event\DomainEvent;

final readonly class CartItemAdded extends DomainEvent
{
    private function __construct(
        private CartId $cartId,
        private CartItemId $cartItemId,
        private ProductId $productId,
        private ProductName $productName,
        private Money $unitPrice,
        private Quantity $quantity,
        private \DateTimeImmutable $occurredOn,
    ) {
    }

    public static function create(
        CartId $cartId,
        CartItemId $cartItemId,
        ProductId $productId,
        ProductName $productName,
        Money $unitPrice,
        Quantity $quantity,
        \DateTimeImmutable $occurredOn,
    ): self {
        return new self($cartId, $cartItemId, $productId, $productName, $unitPrice, $quantity, $occurredOn);
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

    public function productName(): ProductName
    {
        return $this->productName;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function quantity(): Quantity
    {
        return $this->quantity;
    }

    public function eventName(): string
    {
        return 'cart.item_added';
    }

    public function toPrimitives(): array
    {
        return [
            'cart_id' => $this->cartId->value(),
            'cart_item_id' => $this->cartItemId->value(),
            'product_id' => $this->productId->value(),
            'product_name' => $this->productName->value(),
            'unit_price_cents' => $this->unitPrice->amountInCents(),
            'currency' => $this->unitPrice->currency(),
            'quantity' => $this->quantity->value(),
        ];
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
