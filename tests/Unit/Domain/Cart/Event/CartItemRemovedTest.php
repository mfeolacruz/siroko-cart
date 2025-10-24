<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Cart\Event;

use App\Domain\Cart\Event\CartItemRemoved;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Shared\ValueObject\ProductId;
use PHPUnit\Framework\TestCase;

final class CartItemRemovedTest extends TestCase
{
    public function testItCanBeCreated(): void
    {
        $cartId = CartId::generate();
        $cartItemId = CartItemId::generate();
        $productId = ProductId::generate();

        $event = CartItemRemoved::create($cartId, $cartItemId, $productId);

        $this->assertEquals($cartId, $event->cartId());
        $this->assertEquals($cartItemId, $event->cartItemId());
        $this->assertEquals($productId, $event->productId());
        $this->assertInstanceOf(\DateTimeImmutable::class, $event->occurredOn());
    }

    public function testItHasCorrectEventName(): void
    {
        $cartId = CartId::generate();
        $cartItemId = CartItemId::generate();
        $productId = ProductId::generate();

        $event = CartItemRemoved::create($cartId, $cartItemId, $productId);

        $this->assertEquals('cart.item.removed', $event->eventName());
    }

    public function testItCanBeConvertedToPrimitives(): void
    {
        $cartId = CartId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $cartItemId = CartItemId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $productId = ProductId::fromString('550e8400-e29b-41d4-a716-446655440002');

        $event = CartItemRemoved::create($cartId, $cartItemId, $productId);
        $primitives = $event->toPrimitives();
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $primitives['cart_id']);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440001', $primitives['cart_item_id']);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440002', $primitives['product_id']);
        $this->assertArrayHasKey('occurred_on', $primitives);
        $this->assertIsString($primitives['occurred_on']);
    }
}
