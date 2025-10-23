<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Cart\Entity;

use App\Domain\Cart\Entity\CartItem;
use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Cart\ValueObject\Money;
use App\Domain\Cart\ValueObject\ProductId;
use App\Domain\Cart\ValueObject\ProductName;
use App\Domain\Cart\ValueObject\Quantity;
use PHPUnit\Framework\TestCase;

final class CartItemTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $id = CartItemId::generate();
        $productId = ProductId::generate();
        $name = ProductName::fromString('Gafas Siroko Tech K3');
        $unitPrice = Money::fromCents(4999, 'EUR');
        $quantity = Quantity::fromInt(2);

        $cartItem = CartItem::create($id, $productId, $name, $unitPrice, $quantity);

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

        $cartItem = CartItem::create($id, $productId, $name, $unitPrice, $quantity);
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

        $cartItem = CartItem::create($id, $productId, $name, $unitPrice, $quantity);
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

        $cartItem = CartItem::create($id, $productId, $name, $unitPrice, $quantity);
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
            $productId,
            $name,
            $unitPrice,
            Quantity::fromInt(2)
        );

        $cartItem2 = CartItem::create(
            CartItemId::generate(),
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
            ProductId::generate(),
            $name,
            $unitPrice,
            Quantity::fromInt(2)
        );

        $cartItem2 = CartItem::create(
            CartItemId::generate(),
            ProductId::generate(),
            $name,
            $unitPrice,
            Quantity::fromInt(3)
        );

        $this->assertFalse($cartItem1->isSameProduct($cartItem2));
    }
}
