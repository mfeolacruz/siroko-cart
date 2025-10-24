<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Cart\Entity;

use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Entity\CartItem;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Shared\ValueObject\ProductId;
use App\Domain\Shared\ValueObject\ProductName;
use App\Domain\Shared\ValueObject\Quantity;
use PHPUnit\Framework\TestCase;

final class CartItemTest extends TestCase
{
    private Cart $cart;

    protected function setUp(): void
    {
        // Create a cart fixture for all tests
        $this->cart = Cart::create(CartId::generate());
    }

    public function testCanBeCreated(): void
    {
        $id = CartItemId::generate();
        $productId = ProductId::generate();
        $name = ProductName::fromString('Gafas Siroko Tech K3');
        $unitPrice = Money::fromCents(4999, 'EUR');
        $quantity = Quantity::fromInt(2);

        $cartItem = CartItem::create($id, $this->cart, $productId, $name, $unitPrice, $quantity);

        $this->assertInstanceOf(CartItem::class, $cartItem);
        $this->assertTrue($cartItem->id()->equals($id));
        $this->assertTrue($cartItem->productId()->equals($productId));
        $this->assertTrue($cartItem->name()->equals($name));
        $this->assertTrue($cartItem->unitPrice()->equals($unitPrice));
        $this->assertTrue($cartItem->quantity()->equals($quantity));
    }

    public function testCalculatesSubtotalCorrectly(): void
    {
        $id = CartItemId::generate();
        $productId = ProductId::generate();
        $name = ProductName::fromString('Gafas Siroko Tech K3');
        $unitPrice = Money::fromCents(4999, 'EUR'); // 49.99€
        $quantity = Quantity::fromInt(3);

        $cartItem = CartItem::create($id, $this->cart, $productId, $name, $unitPrice, $quantity);
        $subtotal = $cartItem->subtotal();

        $this->assertEquals(14997, $subtotal->amountInCents()); // 49.99 * 3 = 149.97€
        $this->assertEquals('EUR', $subtotal->currency());
    }

    public function testCanIncreaseQuantity(): void
    {
        $id = CartItemId::generate();
        $productId = ProductId::generate();
        $name = ProductName::fromString('Gafas Siroko');
        $unitPrice = Money::fromCents(5000, 'EUR');
        $quantity = Quantity::fromInt(2);

        $cartItem = CartItem::create($id, $this->cart, $productId, $name, $unitPrice, $quantity);
        $cartItem->increaseQuantity(Quantity::fromInt(3));

        $this->assertEquals(5, $cartItem->quantity()->value());
    }

    public function testCanUpdateQuantity(): void
    {
        $id = CartItemId::generate();
        $productId = ProductId::generate();
        $name = ProductName::fromString('Gafas Siroko');
        $unitPrice = Money::fromCents(5000, 'EUR');
        $quantity = Quantity::fromInt(2);

        $cartItem = CartItem::create($id, $this->cart, $productId, $name, $unitPrice, $quantity);
        $cartItem->updateQuantity(Quantity::fromInt(5));

        $this->assertEquals(5, $cartItem->quantity()->value());
    }

    public function testTwoCartItemsWithSameProductIdAreComparable(): void
    {
        $productId = ProductId::generate();
        $name = ProductName::fromString('Gafas Siroko');
        $unitPrice = Money::fromCents(5000, 'EUR');

        $cartItem1 = CartItem::create(
            CartItemId::generate(),
            $this->cart,
            $productId,
            $name,
            $unitPrice,
            Quantity::fromInt(2)
        );

        $cartItem2 = CartItem::create(
            CartItemId::generate(),
            $this->cart,
            $productId,
            $name,
            $unitPrice,
            Quantity::fromInt(3)
        );

        $this->assertTrue($cartItem1->isSameProduct($cartItem2));
    }

    public function testTwoCartItemsWithDifferentProductIdAreNotSameProduct(): void
    {
        $name = ProductName::fromString('Gafas Siroko');
        $unitPrice = Money::fromCents(5000, 'EUR');

        $cartItem1 = CartItem::create(
            CartItemId::generate(),
            $this->cart,
            ProductId::generate(),
            $name,
            $unitPrice,
            Quantity::fromInt(2)
        );

        $cartItem2 = CartItem::create(
            CartItemId::generate(),
            $this->cart,
            ProductId::generate(),
            $name,
            $unitPrice,
            Quantity::fromInt(3)
        );

        $this->assertFalse($cartItem1->isSameProduct($cartItem2));
    }

    public function testHasCreatedAtTimestamp(): void
    {
        $cartItem = CartItem::create(
            CartItemId::generate(),
            $this->cart,
            ProductId::generate(),
            ProductName::fromString('Gafas Siroko'),
            Money::fromCents(5000, 'EUR'),
            Quantity::fromInt(1)
        );

        $this->assertInstanceOf(\DateTimeImmutable::class, $cartItem->createdAt());
        $this->assertLessThanOrEqual(new \DateTimeImmutable(), $cartItem->createdAt());
    }

    public function testHasUpdatedAtTimestamp(): void
    {
        $cartItem = CartItem::create(
            CartItemId::generate(),
            $this->cart,
            ProductId::generate(),
            ProductName::fromString('Gafas Siroko'),
            Money::fromCents(5000, 'EUR'),
            Quantity::fromInt(1)
        );

        $this->assertInstanceOf(\DateTimeImmutable::class, $cartItem->updatedAt());
        $this->assertLessThanOrEqual(new \DateTimeImmutable(), $cartItem->updatedAt());
    }

    public function testUpdatedAtChangesWhenQuantityIncreases(): void
    {
        $cartItem = CartItem::create(
            CartItemId::generate(),
            $this->cart,
            ProductId::generate(),
            ProductName::fromString('Gafas Siroko'),
            Money::fromCents(5000, 'EUR'),
            Quantity::fromInt(1)
        );

        $originalUpdatedAt = $cartItem->updatedAt();

        sleep(1); // Wait 1 second to ensure timestamp difference

        $cartItem->increaseQuantity(Quantity::fromInt(1));

        $this->assertGreaterThan(
            $originalUpdatedAt->getTimestamp(),
            $cartItem->updatedAt()->getTimestamp()
        );
    }

    public function testUpdatedAtChangesWhenQuantityUpdates(): void
    {
        $cartItem = CartItem::create(
            CartItemId::generate(),
            $this->cart,
            ProductId::generate(),
            ProductName::fromString('Gafas Siroko'),
            Money::fromCents(5000, 'EUR'),
            Quantity::fromInt(1)
        );

        $originalUpdatedAt = $cartItem->updatedAt();

        sleep(1); // Wait 1 second to ensure timestamp difference

        $cartItem->updateQuantity(Quantity::fromInt(5));

        $this->assertGreaterThan(
            $originalUpdatedAt->getTimestamp(),
            $cartItem->updatedAt()->getTimestamp()
        );
    }
}
