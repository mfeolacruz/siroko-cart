<?php

declare(strict_types=1);

namespace App\Domain\Checkout\Aggregate;

use App\Domain\Checkout\Entity\OrderItem;
use App\Domain\Checkout\Event\OrderCreated;
use App\Domain\Checkout\ValueObject\OrderId;
use App\Domain\Checkout\ValueObject\OrderItemId;
use App\Domain\Checkout\ValueObject\OrderStatus;
use App\Domain\Shared\Aggregate\AggregateRoot;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Shared\ValueObject\ProductId;
use App\Domain\Shared\ValueObject\ProductName;
use App\Domain\Shared\ValueObject\Quantity;
use App\Domain\Shared\ValueObject\UserId;

class Order extends AggregateRoot
{
    /** @var array<string, OrderItem> */
    private array $items = [];

    private function __construct(
        private readonly OrderId $id,
        private readonly ?UserId $userId,
        private OrderStatus $status,
        private Money $total,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(OrderId $id, ?UserId $userId = null): self
    {
        $createdAt = new \DateTimeImmutable();
        $status = OrderStatus::pending();
        $total = Money::fromCents(0, 'EUR');

        $order = new self(
            $id,
            $userId,
            $status,
            $total,
            $createdAt,
            $createdAt
        );

        $order->record(new OrderCreated($id, $userId, $createdAt));

        return $order;
    }

    /**
     * @param array<string, OrderItem> $items
     */
    public static function reconstruct(
        OrderId $id,
        ?UserId $userId,
        OrderStatus $status,
        Money $total,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        array $items,
    ): self {
        $order = new self($id, $userId, $status, $total, $createdAt, $updatedAt);
        $order->items = $items;

        return $order;
    }

    public function id(): OrderId
    {
        return $this->id;
    }

    public function userId(): ?UserId
    {
        return $this->userId;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    public function total(): Money
    {
        return $this->total;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * @return array<OrderItem>
     */
    public function items(): array
    {
        return array_values($this->items);
    }

    public function changeStatus(OrderStatus $status): void
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function captureTotal(Money $total): void
    {
        $this->total = $total;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function addItem(
        OrderItemId $itemId,
        ProductId $productId,
        ProductName $productName,
        Money $unitPrice,
        Quantity $quantity,
    ): void {
        $orderItem = OrderItem::create(
            $itemId,
            $this,
            $productId,
            $productName,
            $unitPrice,
            $quantity
        );

        $this->items[$itemId->value()] = $orderItem;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function calculateTotal(): Money
    {
        if (empty($this->items)) {
            return Money::fromCents(0, 'EUR');
        }

        $totalCents = 0;
        foreach ($this->items as $item) {
            $totalCents += $item->subtotal()->amountInCents();
        }

        return Money::fromCents($totalCents, 'EUR');
    }
}
