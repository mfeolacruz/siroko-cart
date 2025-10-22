<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Cart\Aggregate;

use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class CartTest extends TestCase
{
    public function testCartIsCreatedWithUniqueId(): void
    {
        $cartId = CartId::generate();

        $cart = Cart::create($cartId);

        $this->assertEquals($cartId, $cart->id());
    }

    public function testCartIsCreatedEmpty(): void
    {
        $cartId = CartId::generate();

        $cart = Cart::create($cartId);

        $this->assertTrue($cart->isEmpty());
        $this->assertCount(0, $cart->items());
    }

    public function testCartHasCreatedAt(): void
    {
        $cartId = CartId::generate();

        $cart = Cart::create($cartId);

        $this->assertInstanceOf(\DateTimeImmutable::class, $cart->createdAt());
        $this->assertEqualsWithDelta(time(), $cart->createdAt()->getTimestamp(), 2);
    }

    public function testCartHasExpiresAt(): void
    {
        $cartId = CartId::generate();

        $cart = Cart::create($cartId);

        $this->assertInstanceOf(\DateTimeImmutable::class, $cart->expiresAt());
        // Cart expires in 7 days
        $expectedExpiration = (new \DateTimeImmutable())->modify('+7 days');
        $this->assertEqualsWithDelta(
            $expectedExpiration->getTimestamp(),
            $cart->expiresAt()->getTimestamp(),
            2
        );
    }

    public function testAnonymousCartHasNoUser(): void
    {
        $cartId = CartId::generate();

        $cart = Cart::create($cartId);

        $this->assertNull($cart->userId());
        $this->assertTrue($cart->isAnonymous());
    }

    public function testCartCanBelongToUser(): void
    {
        $cartId = CartId::generate();
        $userId = UserId::generate();

        $cart = Cart::create($cartId, $userId);

        $this->assertEquals($userId, $cart->userId());
        $this->assertFalse($cart->isAnonymous());
    }
}
