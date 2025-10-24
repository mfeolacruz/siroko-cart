<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order\Entity;

use App\Domain\Checkout\Aggregate\Order;
use App\Domain\Checkout\Entity\OrderItem;
use App\Domain\Checkout\ValueObject\OrderId;
use App\Domain\Checkout\ValueObject\OrderItemId;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Shared\ValueObject\ProductId;
use App\Domain\Shared\ValueObject\ProductName;
use App\Domain\Shared\ValueObject\Quantity;
use App\Domain\Shared\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class OrderItemTest extends TestCase
{
    public function testItCanBeCreated(): void
    {
        $orderItemId = OrderItemId::generate();
        $orderId = OrderId::generate();
        $userId = UserId::generate();
        $order = Order::create($orderId, $userId);
        $productId = ProductId::generate();
        $productName = ProductName::fromString('Test Product');
        $unitPrice = Money::fromCents(2999, 'EUR');
        $quantity = Quantity::fromInt(2);

        $orderItem = OrderItem::create(
            $orderItemId,
            $order,
            $productId,
            $productName,
            $unitPrice,
            $quantity
        );

        $this->assertTrue($orderItem->id()->equals($orderItemId));
        $this->assertSame($order, $orderItem->order());
        $this->assertTrue($orderItem->productId()->equals($productId));
        $this->assertTrue($orderItem->name()->equals($productName));
        $this->assertTrue($orderItem->unitPrice()->equals($unitPrice));
        $this->assertTrue($orderItem->quantity()->equals($quantity));
        $this->assertInstanceOf(\DateTimeImmutable::class, $orderItem->createdAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $orderItem->updatedAt());
    }

    public function testItCalculatesSubtotalCorrectly(): void
    {
        $orderItemId = OrderItemId::generate();
        $orderId = OrderId::generate();
        $userId = UserId::generate();
        $order = Order::create($orderId, $userId);
        $productId = ProductId::generate();
        $productName = ProductName::fromString('Test Product');
        $unitPrice = Money::fromCents(2999, 'EUR');
        $quantity = Quantity::fromInt(3);

        $orderItem = OrderItem::create(
            $orderItemId,
            $order,
            $productId,
            $productName,
            $unitPrice,
            $quantity
        );

        $expectedSubtotal = Money::fromCents(8997, 'EUR');
        $this->assertTrue($orderItem->subtotal()->equals($expectedSubtotal));
    }

    public function testItCanCompareSameProduct(): void
    {
        $orderItemId1 = OrderItemId::generate();
        $orderItemId2 = OrderItemId::generate();
        $orderId = OrderId::generate();
        $userId = UserId::generate();
        $order = Order::create($orderId, $userId);
        $productId = ProductId::generate();
        $differentProductId = ProductId::generate();
        $productName = ProductName::fromString('Test Product');
        $unitPrice = Money::fromCents(2999, 'EUR');
        $quantity = Quantity::fromInt(1);

        $orderItem1 = OrderItem::create(
            $orderItemId1,
            $order,
            $productId,
            $productName,
            $unitPrice,
            $quantity
        );

        $orderItem2 = OrderItem::create(
            $orderItemId2,
            $order,
            $productId,
            $productName,
            $unitPrice,
            $quantity
        );

        $orderItem3 = OrderItem::create(
            $orderItemId2,
            $order,
            $differentProductId,
            $productName,
            $unitPrice,
            $quantity
        );

        $this->assertTrue($orderItem1->isSameProduct($orderItem2));
        $this->assertFalse($orderItem1->isSameProduct($orderItem3));
    }

    public function testCreatedAtAndUpdatedAtAreSetToSameTimeOnCreation(): void
    {
        $orderItemId = OrderItemId::generate();
        $orderId = OrderId::generate();
        $userId = UserId::generate();
        $order = Order::create($orderId, $userId);
        $productId = ProductId::generate();
        $productName = ProductName::fromString('Test Product');
        $unitPrice = Money::fromCents(2999, 'EUR');
        $quantity = Quantity::fromInt(1);

        $orderItem = OrderItem::create(
            $orderItemId,
            $order,
            $productId,
            $productName,
            $unitPrice,
            $quantity
        );

        $this->assertEquals($orderItem->createdAt(), $orderItem->updatedAt());
    }

    public function testItPreservesCreationTimestamps(): void
    {
        $orderItemId = OrderItemId::generate();
        $orderId = OrderId::generate();
        $userId = UserId::generate();
        $order = Order::create($orderId, $userId);
        $productId = ProductId::generate();
        $productName = ProductName::fromString('Test Product');
        $unitPrice = Money::fromCents(2999, 'EUR');
        $quantity = Quantity::fromInt(1);

        $beforeCreation = new \DateTimeImmutable();
        $orderItem = OrderItem::create(
            $orderItemId,
            $order,
            $productId,
            $productName,
            $unitPrice,
            $quantity
        );
        $afterCreation = new \DateTimeImmutable();

        $this->assertGreaterThanOrEqual($beforeCreation, $orderItem->createdAt());
        $this->assertLessThanOrEqual($afterCreation, $orderItem->createdAt());
    }

    public function testItCanBeReconstructedFromPersistence(): void
    {
        $orderItemId = OrderItemId::generate();
        $orderId = OrderId::generate();
        $userId = UserId::generate();
        $order = Order::create($orderId, $userId);
        $productId = ProductId::generate();
        $productName = ProductName::fromString('Reconstructed Product');
        $unitPrice = Money::fromCents(1500, 'EUR');
        $quantity = Quantity::fromInt(2);
        $createdAt = new \DateTimeImmutable('2024-01-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-01 11:00:00');

        $orderItem = OrderItem::reconstruct(
            $orderItemId,
            $order,
            $productId,
            $productName,
            $unitPrice,
            $quantity,
            $createdAt,
            $updatedAt
        );

        $this->assertTrue($orderItem->id()->equals($orderItemId));
        $this->assertSame($order, $orderItem->order());
        $this->assertTrue($orderItem->productId()->equals($productId));
        $this->assertTrue($orderItem->name()->equals($productName));
        $this->assertTrue($orderItem->unitPrice()->equals($unitPrice));
        $this->assertTrue($orderItem->quantity()->equals($quantity));
        $this->assertEquals($createdAt, $orderItem->createdAt());
        $this->assertEquals($updatedAt, $orderItem->updatedAt());
    }
}
