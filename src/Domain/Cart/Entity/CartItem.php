<?php

declare(strict_types=1);

namespace App\Domain\Cart\Entity;

use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Shared\ValueObject\ProductId;
use App\Domain\Shared\ValueObject\ProductName;
use App\Domain\Shared\ValueObject\Quantity;

final class CartItem
{
    private function __construct(
        private readonly CartItemId $id,
        private readonly Cart $cart,
        private readonly ProductId $productId,
        private readonly ProductName $name,
        private readonly Money $unitPrice,
        private Quantity $quantity,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        CartItemId $id,
        Cart $cart,
        ProductId $productId,
        ProductName $name,
        Money $unitPrice,
        Quantity $quantity,
    ): self {
        $now = new \DateTimeImmutable();

        return new self($id, $cart, $productId, $name, $unitPrice, $quantity, $now, $now);
    }

    public function id(): CartItemId
    {
        return $this->id;
    }

    public function productId(): ProductId
    {
        return $this->productId;
    }

    public function name(): ProductName
    {
        return $this->name;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function quantity(): Quantity
    {
        return $this->quantity;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function cart(): Cart
    {
        return $this->cart;
    }

    public function subtotal(): Money
    {
        return $this->unitPrice->multiply($this->quantity->value());
    }

    public function increaseQuantity(Quantity $amount): void
    {
        $this->quantity = $this->quantity->increase($amount);
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateQuantity(Quantity $newQuantity): void
    {
        $this->quantity = $newQuantity;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isSameProduct(self $other): bool
    {
        return $this->productId->equals($other->productId);
    }
}
