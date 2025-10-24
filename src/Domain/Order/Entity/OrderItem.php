<?php

declare(strict_types=1);

namespace App\Domain\Order\Entity;

use App\Domain\Order\Aggregate\Order;
use App\Domain\Order\ValueObject\OrderItemId;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Shared\ValueObject\ProductId;
use App\Domain\Shared\ValueObject\ProductName;
use App\Domain\Shared\ValueObject\Quantity;

final class OrderItem
{
    private function __construct(
        private readonly OrderItemId $id,
        private readonly Order $order,
        private readonly ProductId $productId,
        private readonly ProductName $name,
        private readonly Money $unitPrice,
        private readonly Quantity $quantity,
        private readonly \DateTimeImmutable $createdAt,
        private readonly \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        OrderItemId $id,
        Order $order,
        ProductId $productId,
        ProductName $name,
        Money $unitPrice,
        Quantity $quantity,
    ): self {
        $now = new \DateTimeImmutable();

        return new self($id, $order, $productId, $name, $unitPrice, $quantity, $now, $now);
    }

    public static function reconstruct(
        OrderItemId $id,
        Order $order,
        ProductId $productId,
        ProductName $name,
        Money $unitPrice,
        Quantity $quantity,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $order, $productId, $name, $unitPrice, $quantity, $createdAt, $updatedAt);
    }

    public function id(): OrderItemId
    {
        return $this->id;
    }

    public function order(): Order
    {
        return $this->order;
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

    public function subtotal(): Money
    {
        return $this->unitPrice->multiply($this->quantity->value());
    }

    public function isSameProduct(self $other): bool
    {
        return $this->productId->equals($other->productId);
    }
}
