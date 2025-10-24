<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Cart\Event;

use App\Domain\Cart\Event\CartItemAdded;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Shared\Event\DomainEvent;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Shared\ValueObject\ProductId;
use App\Domain\Shared\ValueObject\ProductName;
use App\Domain\Shared\ValueObject\Quantity;
use PHPUnit\Framework\TestCase;

final class CartItemAddedTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $cartId = CartId::generate();
        $cartItemId = CartItemId::generate();
        $productId = ProductId::generate();
        $productName = ProductName::fromString('Gafas Siroko Tech K3');
        $unitPrice = Money::fromCents(4999, 'EUR');
        $quantity = Quantity::fromInt(2);
        $occurredOn = new \DateTimeImmutable();

        $event = CartItemAdded::create(
            $cartId,
            $cartItemId,
            $productId,
            $productName,
            $unitPrice,
            $quantity,
            $occurredOn
        );

        $this->assertInstanceOf(CartItemAdded::class, $event);
        $this->assertInstanceOf(DomainEvent::class, $event);
        $this->assertTrue($event->cartId()->equals($cartId));
        $this->assertTrue($event->cartItemId()->equals($cartItemId));
        $this->assertTrue($event->productId()->equals($productId));
        $this->assertTrue($event->productName()->equals($productName));
        $this->assertTrue($event->unitPrice()->equals($unitPrice));
        $this->assertTrue($event->quantity()->equals($quantity));
        $this->assertEquals(
            $occurredOn->getTimestamp(),
            $event->occurredOn()->getTimestamp()
        );
    }

    public function testEventNameIsCorrect(): void
    {
        $event = CartItemAdded::create(
            CartId::generate(),
            CartItemId::generate(),
            ProductId::generate(),
            ProductName::fromString('Gafas Siroko'),
            Money::fromCents(5000, 'EUR'),
            Quantity::fromInt(1),
            new \DateTimeImmutable()
        );

        $this->assertEquals('cart.item_added', $event->eventName());
    }

    public function testCanBeConvertedToArray(): void
    {
        $cartId = CartId::generate();
        $cartItemId = CartItemId::generate();
        $productId = ProductId::generate();
        $productName = ProductName::fromString('Gafas Siroko');
        $unitPrice = Money::fromCents(5000, 'EUR');
        $quantity = Quantity::fromInt(2);
        $occurredOn = new \DateTimeImmutable();

        $event = CartItemAdded::create(
            $cartId,
            $cartItemId,
            $productId,
            $productName,
            $unitPrice,
            $quantity,
            $occurredOn
        );

        $array = $event->toPrimitives();

        $this->assertEquals($cartId->value(), $array['cart_id']);
        $this->assertEquals($cartItemId->value(), $array['cart_item_id']);
        $this->assertEquals($productId->value(), $array['product_id']);
        $this->assertEquals($productName->value(), $array['product_name']);
        $this->assertEquals($unitPrice->amountInCents(), $array['unit_price_cents']);
        $this->assertEquals($unitPrice->currency(), $array['currency']);
        $this->assertEquals($quantity->value(), $array['quantity']);
    }
}
