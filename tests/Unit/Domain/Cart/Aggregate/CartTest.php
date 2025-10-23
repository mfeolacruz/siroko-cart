<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Cart\Aggregate;

use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Entity\CartItem;
use App\Domain\Cart\Event\CartCreated;
use App\Domain\Cart\Event\CartItemAdded;
use App\Domain\Cart\Event\CartItemQuantityUpdated;
use App\Domain\Cart\Event\CartItemRemoved;
use App\Domain\Cart\Exception\CartItemNotFoundException;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\CartItemId;
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

    public function testFindItemByIdReturnsCorrectItem(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        // Add multiple items
        $productId1 = ProductId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $productId2 = ProductId::fromString('550e8400-e29b-41d4-a716-446655440002');

        $cart->addItem(
            $productId1,
            ProductName::fromString('Product 1'),
            Money::fromCents(1999, 'EUR'),
            Quantity::fromInt(1)
        );

        $cart->addItem(
            $productId2,
            ProductName::fromString('Product 2'),
            Money::fromCents(2999, 'EUR'),
            Quantity::fromInt(2)
        );

        $items = $cart->items();
        $firstItemId = $items[0]->id();
        $secondItemId = $items[1]->id();

        // Test finding first item
        $foundItem = $cart->findItemById($firstItemId);
        $this->assertNotNull($foundItem);
        $this->assertEquals($firstItemId, $foundItem->id());
        $this->assertEquals('Product 1', $foundItem->name()->value());

        // Test finding second item
        $foundItem = $cart->findItemById($secondItemId);
        $this->assertNotNull($foundItem);
        $this->assertEquals($secondItemId, $foundItem->id());
        $this->assertEquals('Product 2', $foundItem->name()->value());
    }

    public function testFindItemByIdReturnsNullWhenNotFound(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        $nonExistentItemId = CartItemId::generate();

        $foundItem = $cart->findItemById($nonExistentItemId);
        $this->assertNull($foundItem);
    }

    public function testUpdateItemQuantityChangesExistingItemQuantity(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        // Add an item first
        $productId = ProductId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $cart->addItem(
            $productId,
            ProductName::fromString('Test Product'),
            Money::fromCents(2999, 'EUR'),
            Quantity::fromInt(2)
        );

        // Get the item ID
        $items = $cart->items();
        $cartItemId = $items[0]->id();

        // Update the quantity
        $cart->updateItemQuantity($cartItemId, Quantity::fromInt(5));

        // Verify the quantity was updated
        $updatedItems = $cart->items();
        $this->assertEquals(5, $updatedItems[0]->quantity()->value());
    }

    public function testUpdateItemQuantityThrowsExceptionWhenItemNotFound(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        $nonExistentItemId = CartItemId::generate();

        $this->expectException(CartItemNotFoundException::class);

        $cart->updateItemQuantity($nonExistentItemId, Quantity::fromInt(5));
    }

    public function testUpdateItemQuantityRecordsCartItemQuantityUpdatedEvent(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        // Add an item first
        $productId = ProductId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $cart->addItem(
            $productId,
            ProductName::fromString('Test Product'),
            Money::fromCents(2999, 'EUR'),
            Quantity::fromInt(2)
        );

        // Clear events to focus on update event
        $cart->pullDomainEvents();

        // Get the item ID and update quantity
        $items = $cart->items();
        $cartItemId = $items[0]->id();
        $cart->updateItemQuantity($cartItemId, Quantity::fromInt(5));

        // Verify event was recorded
        $events = $cart->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(CartItemQuantityUpdated::class, $events[0]);
    }

    public function testItCanRemoveAnItem(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        // Add two items first
        $product1Id = ProductId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $cart->addItem(
            $product1Id,
            ProductName::fromString('Product 1'),
            Money::fromCents(2999, 'EUR'),
            Quantity::fromInt(2)
        );

        $product2Id = ProductId::fromString('550e8400-e29b-41d4-a716-446655440002');
        $cart->addItem(
            $product2Id,
            ProductName::fromString('Product 2'),
            Money::fromCents(1999, 'EUR'),
            Quantity::fromInt(1)
        );

        $this->assertCount(2, $cart->items());
        $this->assertEquals(7997, $cart->total()->amountInCents()); // (2999*2) + (1999*1)

        // Get the first item ID and remove it
        $items = $cart->items();
        $itemToRemoveId = $items[0]->id();

        $cart->removeItem($itemToRemoveId);

        // Verify item was removed
        $this->assertCount(1, $cart->items());
        $this->assertEquals(1999, $cart->total()->amountInCents()); // Only product 2 remains

        // Verify the remaining item is the second one
        $remainingItems = $cart->items();
        $this->assertTrue($remainingItems[0]->productId()->equals($product2Id));
    }

    public function testRemoveItemThrowsExceptionWhenItemNotFound(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        $nonExistentItemId = CartItemId::generate();

        $this->expectException(CartItemNotFoundException::class);
        $this->expectExceptionMessage('Cart item with id "'.$nonExistentItemId->value().'" was not found');

        $cart->removeItem($nonExistentItemId);
    }

    public function testRemoveLastItemMakesCartEmpty(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        // Add one item
        $productId = ProductId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $cart->addItem(
            $productId,
            ProductName::fromString('Test Product'),
            Money::fromCents(2999, 'EUR'),
            Quantity::fromInt(1)
        );

        $this->assertFalse($cart->isEmpty());
        $this->assertEquals(1, $cart->totalItems());

        // Remove the only item
        $items = $cart->items();
        $cart->removeItem($items[0]->id());

        // Verify cart is empty
        $this->assertTrue($cart->isEmpty());
        $this->assertEquals(0, $cart->totalItems());
        $this->assertEquals(0, $cart->total()->amountInCents());
        $this->assertCount(0, $cart->items());
    }

    public function testRemoveItemRecordsCartItemRemovedEvent(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        // Add an item first
        $productId = ProductId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $cart->addItem(
            $productId,
            ProductName::fromString('Test Product'),
            Money::fromCents(2999, 'EUR'),
            Quantity::fromInt(1)
        );

        // Clear events to focus on remove event
        $cart->pullDomainEvents();

        // Remove the item
        $items = $cart->items();
        $cart->removeItem($items[0]->id());

        // Verify event was recorded
        $events = $cart->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(CartItemRemoved::class, $events[0]);
    }
}
