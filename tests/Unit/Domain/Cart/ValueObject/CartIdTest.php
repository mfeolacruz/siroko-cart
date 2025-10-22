<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Cart\ValueObject;

use App\Domain\Cart\ValueObject\CartId;
use PHPUnit\Framework\TestCase;

final class CartIdTest extends TestCase
{
    public function testItGeneratesValidUuid(): void
    {
        $cartId = CartId::generate();

        $this->assertInstanceOf(CartId::class, $cartId);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $cartId->value()
        );
    }

    public function testItCreatesFromValidString(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $cartId = CartId::fromString($uuid);

        $this->assertEquals($uuid, $cartId->value());
    }

    public function testItThrowsExceptionForInvalidUuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CartId::fromString('invalid-uuid');
    }

    public function testItComparesEquality(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $cartId1 = CartId::fromString($uuid);
        $cartId2 = CartId::fromString($uuid);
        $cartId3 = CartId::generate();

        $this->assertTrue($cartId1->equals($cartId2));
        $this->assertFalse($cartId1->equals($cartId3));
    }

    public function testItConvertsToString(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $cartId = CartId::fromString($uuid);

        $this->assertEquals($uuid, (string) $cartId);
    }
}
