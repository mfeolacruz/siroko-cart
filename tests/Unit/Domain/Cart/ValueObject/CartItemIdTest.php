<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Cart\ValueObject;

use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Shared\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class CartItemIdTest extends TestCase
{
    public function testCanBeGenerated(): void
    {
        $cartItemId = CartItemId::generate();

        $this->assertInstanceOf(CartItemId::class, $cartItemId);
        $this->assertInstanceOf(Uuid::class, $cartItemId);
        $this->assertNotEmpty($cartItemId->value());
    }

    public function testCanBeCreatedFromString(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $cartItemId = CartItemId::fromString($uuidString);

        $this->assertInstanceOf(CartItemId::class, $cartItemId);
        $this->assertEquals($uuidString, $cartItemId->value());
    }

    public function testTwoCartItemIdsWithSameValueAreEqual(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $cartItemId1 = CartItemId::fromString($uuidString);
        $cartItemId2 = CartItemId::fromString($uuidString);

        $this->assertTrue($cartItemId1->equals($cartItemId2));
    }

    public function testTwoCartItemIdsWithDifferentValueAreNotEqual(): void
    {
        $cartItemId1 = CartItemId::generate();
        $cartItemId2 = CartItemId::generate();

        $this->assertFalse($cartItemId1->equals($cartItemId2));
    }
}
