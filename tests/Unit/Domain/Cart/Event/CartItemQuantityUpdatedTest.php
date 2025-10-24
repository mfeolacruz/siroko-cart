<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Cart\Event;

use App\Domain\Cart\Event\CartItemQuantityUpdated;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Shared\ValueObject\Quantity;
use PHPUnit\Framework\TestCase;

final class CartItemQuantityUpdatedTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $cartId = CartId::generate();
        $cartItemId = CartItemId::generate();
        $previousQuantity = Quantity::fromInt(2);
        $newQuantity = Quantity::fromInt(5);
        $occurredOn = new \DateTimeImmutable();

        $event = CartItemQuantityUpdated::create(
            $cartId,
            $cartItemId,
            $previousQuantity,
            $newQuantity,
            $occurredOn
        );

        $this->assertInstanceOf(CartItemQuantityUpdated::class, $event);
        $this->assertEquals($cartId, $event->cartId());
        $this->assertEquals($cartItemId, $event->cartItemId());
        $this->assertEquals($previousQuantity, $event->previousQuantity());
        $this->assertEquals($newQuantity, $event->newQuantity());
        $this->assertEquals($occurredOn, $event->occurredOn());
    }

    public function testHasCorrectEventName(): void
    {
        $event = CartItemQuantityUpdated::create(
            CartId::generate(),
            CartItemId::generate(),
            Quantity::fromInt(2),
            Quantity::fromInt(5),
            new \DateTimeImmutable()
        );

        $this->assertEquals('cart.item_quantity_updated', $event->eventName());
    }

    public function testCanBeConvertedToPrimitives(): void
    {
        $cartId = CartId::generate();
        $cartItemId = CartItemId::generate();
        $previousQuantity = Quantity::fromInt(2);
        $newQuantity = Quantity::fromInt(5);

        $event = CartItemQuantityUpdated::create(
            $cartId,
            $cartItemId,
            $previousQuantity,
            $newQuantity,
            new \DateTimeImmutable()
        );

        $primitives = $event->toPrimitives();

        $this->assertEquals($cartId->value(), $primitives['cart_id']);
        $this->assertEquals($cartItemId->value(), $primitives['cart_item_id']);
        $this->assertEquals(2, $primitives['previous_quantity']);
        $this->assertEquals(5, $primitives['new_quantity']);
    }
}
