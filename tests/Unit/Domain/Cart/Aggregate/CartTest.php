<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Cart\Aggregate;

use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Entity\CartItem;
use App\Domain\Cart\Event\CartCreated;
use App\Domain\Cart\Event\CartItemAdded;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\Money;
use App\Domain\Cart\ValueObject\ProductId;
use App\Domain\Cart\ValueObject\ProductName;
use App\Domain\Cart\ValueObject\Quantity;
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

    public function testCartRecordsCreatedEvent(): void
    {
        $cartId = CartId::generate();
        $userId = UserId::generate();

        $cart = Cart::create($cartId, $userId);
        $events = $cart->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(CartCreated::class, $events[0]);
    }

    public function testPullDomainEventsClearsEvents(): void
    {
        $cartId = CartId::generate();

        $cart = Cart::create($cartId);
        $cart->pullDomainEvents(); // First pull
        $events = $cart->pullDomainEvents(); // Second pull

        $this->assertCount(0, $events);
    }

    public function testCanAddItemToCart(): void
    {
        $cart = Cart::create(CartId::generate(), UserId::generate());
        $productId = ProductId::generate();
        $name = ProductName::fromString('Gafas Siroko Tech K3');
        $unitPrice = Money::fromCents(4999, 'EUR');
        $quantity = Quantity::fromInt(2);

        $cart->addItem($productId, $name, $unitPrice, $quantity);

        $this->assertCount(1, $cart->items());
        $this->assertFalse($cart->isEmpty());
    }

    public function testAddingSameProductIncreasesQuantity(): void
    {
        $cart = Cart::create(CartId::generate(), UserId::generate());
        $productId = ProductId::generate();
        $name = ProductName::fromString('Gafas Siroko');
        $unitPrice = Money::fromCents(5000, 'EUR');

        $cart->addItem($productId, $name, $unitPrice, Quantity::fromInt(2));
        $cart->addItem($productId, $name, $unitPrice, Quantity::fromInt(3));

        $this->assertCount(1, $cart->items());
        $items = $cart->items();
        $firstItem = $items[0];
        $this->assertEquals(5, $firstItem->quantity()->value());
    }

    public function testCalculatesTotalCorrectly(): void
    {
        $cart = Cart::create(CartId::generate(), UserId::generate());

        $cart->addItem(
            ProductId::generate(),
            ProductName::fromString('Gafas Siroko'),
            Money::fromCents(4999, 'EUR'),
            Quantity::fromInt(2)
        );

        $cart->addItem(
            ProductId::generate(),
            ProductName::fromString('Casco Aero'),
            Money::fromCents(10000, 'EUR'),
            Quantity::fromInt(1)
        );

        $total = $cart->total();

        $this->assertEquals(19998, $total->amountInCents()); // (49.99 * 2) + (100.00 * 1) = 199.98€
        $this->assertEquals('EUR', $total->currency());
    }

    public function testEmptyCartHasTotalZero(): void
    {
        $cart = Cart::create(CartId::generate(), UserId::generate());

        $total = $cart->total();

        $this->assertEquals(0, $total->amountInCents());
        $this->assertEquals('EUR', $total->currency());
    }

    public function testReturnsItemsArray(): void
    {
        $cart = Cart::create(CartId::generate(), UserId::generate());

        $cart->addItem(
            ProductId::generate(),
            ProductName::fromString('Gafas Siroko'),
            Money::fromCents(4999, 'EUR'),
            Quantity::fromInt(2)
        );

        $items = $cart->items();

        $this->assertCount(1, $items);
        $this->assertInstanceOf(CartItem::class, $items[0]);
    }

    public function testAddingItemRecordsEvent(): void
    {
        $cart = Cart::create(CartId::generate(), UserId::generate());
        $productId = ProductId::generate();
        $name = ProductName::fromString('Gafas Siroko');
        $unitPrice = Money::fromCents(5000, 'EUR');
        $quantity = Quantity::fromInt(2);

        $cart->addItem($productId, $name, $unitPrice, $quantity);

        $events = $cart->pullDomainEvents();
        $this->assertCount(2, $events); // CartCreated + CartItemAdded

        $lastEvent = end($events);
        $this->assertInstanceOf(CartItemAdded::class, $lastEvent);
    }
}
