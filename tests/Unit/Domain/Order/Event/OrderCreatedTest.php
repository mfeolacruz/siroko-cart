<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order\Event;

use App\Domain\Order\Event\OrderCreated;
use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Shared\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class OrderCreatedTest extends TestCase
{
    public function testItCanBeCreatedWithUserId(): void
    {
        $orderId = OrderId::generate();
        $userId = UserId::generate();
        $createdAt = new \DateTimeImmutable('2024-01-01 10:00:00');

        $event = new OrderCreated($orderId, $userId, $createdAt);

        $this->assertTrue($event->orderId()->equals($orderId));
        $this->assertNotNull($event->userId());
        $this->assertTrue($event->userId()->equals($userId));
        $this->assertEquals($createdAt, $event->createdAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $event->occurredOn());
        $this->assertEquals('order.created', $event->eventName());
    }

    public function testItCanBeCreatedWithoutUserId(): void
    {
        $orderId = OrderId::generate();
        $createdAt = new \DateTimeImmutable('2024-01-01 10:00:00');

        $event = new OrderCreated($orderId, null, $createdAt);

        $this->assertTrue($event->orderId()->equals($orderId));
        $this->assertNull($event->userId());
        $this->assertEquals($createdAt, $event->createdAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $event->occurredOn());
        $this->assertEquals('order.created', $event->eventName());
    }

    public function testItCanBeConvertedToPrimitives(): void
    {
        $orderId = OrderId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $createdAt = new \DateTimeImmutable('2024-01-01 10:00:00');

        $event = new OrderCreated($orderId, $userId, $createdAt);

        $primitives = $event->toPrimitives();

        $this->assertArrayHasKey('order_id', $primitives);
        $this->assertArrayHasKey('user_id', $primitives);
        $this->assertArrayHasKey('created_at', $primitives);
        $this->assertArrayHasKey('occurred_on', $primitives);

        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $primitives['order_id']);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440001', $primitives['user_id']);
        $this->assertEquals('2024-01-01T10:00:00+00:00', $primitives['created_at']);
        $this->assertIsString($primitives['occurred_on']);
    }

    public function testItCanBeConvertedToPrimitivesWithoutUserId(): void
    {
        $orderId = OrderId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $createdAt = new \DateTimeImmutable('2024-01-01 10:00:00');

        $event = new OrderCreated($orderId, null, $createdAt);

        $primitives = $event->toPrimitives();

        $this->assertArrayHasKey('order_id', $primitives);
        $this->assertArrayHasKey('user_id', $primitives);
        $this->assertArrayHasKey('created_at', $primitives);
        $this->assertArrayHasKey('occurred_on', $primitives);

        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $primitives['order_id']);
        $this->assertNull($primitives['user_id']);
        $this->assertEquals('2024-01-01T10:00:00+00:00', $primitives['created_at']);
        $this->assertIsString($primitives['occurred_on']);
    }

    public function testOccurredOnIsSetAutomaticallyOnConstruction(): void
    {
        $orderId = OrderId::generate();
        $userId = UserId::generate();
        $createdAt = new \DateTimeImmutable();

        $beforeCreation = new \DateTimeImmutable();
        $event = new OrderCreated($orderId, $userId, $createdAt);
        $afterCreation = new \DateTimeImmutable();

        $this->assertGreaterThanOrEqual($beforeCreation, $event->occurredOn());
        $this->assertLessThanOrEqual($afterCreation, $event->occurredOn());
    }
}
