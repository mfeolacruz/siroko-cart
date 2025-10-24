<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order\Aggregate;

use App\Domain\Checkout\Aggregate\Order;
use App\Domain\Checkout\ValueObject\OrderId;
use App\Domain\Checkout\ValueObject\OrderStatus;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Shared\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    public function testItCanBeCreated(): void
    {
        $orderId = OrderId::generate();
        $userId = UserId::generate();

        $order = Order::create($orderId, $userId);

        $this->assertTrue($order->id()->equals($orderId));
        $this->assertNotNull($order->userId());
        $this->assertTrue($order->userId()->equals($userId));
        $this->assertTrue($order->status()->isPending());
        $this->assertInstanceOf(\DateTimeImmutable::class, $order->createdAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $order->updatedAt());
        $this->assertEquals($order->createdAt(), $order->updatedAt());
    }

    public function testItCanBeCreatedWithoutUserId(): void
    {
        $orderId = OrderId::generate();

        $order = Order::create($orderId, null);

        $this->assertTrue($order->id()->equals($orderId));
        $this->assertNull($order->userId());
        $this->assertTrue($order->status()->isPending());
    }

    public function testItStartsEmpty(): void
    {
        $orderId = OrderId::generate();
        $userId = UserId::generate();

        $order = Order::create($orderId, $userId);

        $this->assertTrue($order->isEmpty());
        $this->assertCount(0, $order->items());
        $this->assertTrue($order->total()->equals(Money::fromCents(0, 'EUR')));
    }

    public function testItCanChangeStatus(): void
    {
        $orderId = OrderId::generate();
        $userId = UserId::generate();
        $order = Order::create($orderId, $userId);

        $beforeUpdate = new \DateTimeImmutable();
        $order->changeStatus(OrderStatus::confirmed());
        $afterUpdate = new \DateTimeImmutable();

        $this->assertTrue($order->status()->isConfirmed());
        $this->assertGreaterThanOrEqual($beforeUpdate, $order->updatedAt());
        $this->assertLessThanOrEqual($afterUpdate, $order->updatedAt());
    }

    public function testItCanCaptureTotal(): void
    {
        $orderId = OrderId::generate();
        $userId = UserId::generate();
        $order = Order::create($orderId, $userId);
        $capturedTotal = Money::fromCents(4999, 'EUR');

        $order->captureTotal($capturedTotal);

        $this->assertTrue($order->total()->equals($capturedTotal));
    }

    public function testItRecordsCreationEvent(): void
    {
        $orderId = OrderId::generate();
        $userId = UserId::generate();

        $order = Order::create($orderId, $userId);

        $events = $order->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(\App\Domain\Checkout\Event\OrderCreated::class, $events[0]);
    }

    public function testItCanBeReconstructed(): void
    {
        $orderId = OrderId::generate();
        $userId = UserId::generate();
        $status = OrderStatus::confirmed();
        $total = Money::fromCents(2999, 'EUR');
        $createdAt = new \DateTimeImmutable('2024-01-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-01 11:00:00');

        $order = Order::reconstruct(
            $orderId,
            $userId,
            $status,
            $total,
            $createdAt,
            $updatedAt,
            []
        );

        $this->assertTrue($order->id()->equals($orderId));
        $this->assertNotNull($order->userId());
        $this->assertTrue($order->userId()->equals($userId));
        $this->assertTrue($order->status()->equals($status));
        $this->assertTrue($order->total()->equals($total));
        $this->assertEquals($createdAt, $order->createdAt());
        $this->assertEquals($updatedAt, $order->updatedAt());
        $this->assertCount(0, $order->items());
    }

    public function testReconstructedOrderDoesNotRecordEvents(): void
    {
        $orderId = OrderId::generate();
        $userId = UserId::generate();
        $status = OrderStatus::pending();
        $total = Money::fromCents(0, 'EUR');
        $createdAt = new \DateTimeImmutable();
        $updatedAt = new \DateTimeImmutable();

        $order = Order::reconstruct(
            $orderId,
            $userId,
            $status,
            $total,
            $createdAt,
            $updatedAt,
            []
        );

        $events = $order->pullDomainEvents();
        $this->assertCount(0, $events);
    }

    public function testItPreservesCreationTimestamps(): void
    {
        $orderId = OrderId::generate();
        $userId = UserId::generate();

        $beforeCreation = new \DateTimeImmutable();
        $order = Order::create($orderId, $userId);
        $afterCreation = new \DateTimeImmutable();

        $this->assertGreaterThanOrEqual($beforeCreation, $order->createdAt());
        $this->assertLessThanOrEqual($afterCreation, $order->createdAt());
    }
}
