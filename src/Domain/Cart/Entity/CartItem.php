<?php

declare(strict_types=1);

namespace App\Domain\Cart\Entity;

use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Cart\ValueObject\Money;
use App\Domain\Cart\ValueObject\ProductId;
use App\Domain\Cart\ValueObject\ProductName;
use App\Domain\Cart\ValueObject\Quantity;

final class CartItem
{
    private function __construct(
        private readonly CartItemId $id,
        private readonly ProductId $productId,
        private readonly ProductName $name,
        private readonly Money $unitPrice,
        private Quantity $quantity,
    ) {
    }

    public static function create(
        CartItemId $id,
        ProductId $productId,
        ProductName $name,
        Money $unitPrice,
        Quantity $quantity,
    ): self {
        return new self($id, $productId, $name, $unitPrice, $quantity);
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

    public function subtotal(): Money
    {
        return $this->unitPrice->multiply($this->quantity->value());
    }

    public function increaseQuantity(Quantity $amount): void
    {
        $this->quantity = $this->quantity->increase($amount);
    }

    public function updateQuantity(Quantity $newQuantity): void
    {
        $this->quantity = $newQuantity;
    }

    public function isSameProduct(self $other): bool
    {
        return $this->productId->equals($other->productId);
    }
}
