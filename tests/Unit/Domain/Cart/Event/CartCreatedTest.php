<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Cart\Event;

use App\Domain\Cart\Event\CartCreated;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class CartCreatedTest extends TestCase
{
    public function testItCreatesEventWithAllData(): void
    {
        $cartId = CartId::generate();
        $userId = UserId::generate();
        $createdAt = new \DateTimeImmutable();

        $event = new CartCreated($cartId, $userId, $createdAt);

        $this->assertEquals($cartId, $event->cartId());
        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($createdAt, $event->createdAt());
        $this->assertEquals('cart.created', $event->eventName());
    }

    public function testItCreatesEventForAnonymousCart(): void
    {
        $cartId = CartId::generate();
        $createdAt = new \DateTimeImmutable();

        $event = new CartCreated($cartId, null, $createdAt);

        $this->assertEquals($cartId, $event->cartId());
        $this->assertNull($event->userId());
        $this->assertEquals($createdAt, $event->createdAt());
    }

    public function testItConvertsToPrimitives(): void
    {
        $cartId = CartId::generate();
        $userId = UserId::generate();
        $createdAt = new \DateTimeImmutable();

        $event = new CartCreated($cartId, $userId, $createdAt);
        $primitives = $event->toPrimitives();

        $this->assertEquals($cartId->value(), $primitives['cart_id']);
        $this->assertEquals($userId->value(), $primitives['user_id']);
        $this->assertEquals($createdAt->format(\DateTimeInterface::ATOM), $primitives['created_at']);
        $this->assertArrayHasKey('occurred_on', $primitives);
    }

    public function testItConvertsToPrimitivesWithNullUserId(): void
    {
        $cartId = CartId::generate();
        $createdAt = new \DateTimeImmutable();

        $event = new CartCreated($cartId, null, $createdAt);
        $primitives = $event->toPrimitives();

        $this->assertNull($primitives['user_id']);
    }
}
