<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Shared\ValueObject\ProductId;
use App\Domain\Shared\ValueObject\ProductName;
use App\Domain\Shared\ValueObject\Quantity;
use App\Domain\Shared\ValueObject\UserId;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineCartRepositoryTest extends KernelTestCase
{
    private CartRepositoryInterface $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $repository = static::getContainer()->get(CartRepositoryInterface::class);
        assert($repository instanceof CartRepositoryInterface);
        $this->repository = $repository;

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        assert($entityManager instanceof EntityManagerInterface);
        $this->entityManager = $entityManager;
    }

    public function testItSavesAndFindsAnonymousCart(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        $this->repository->save($cart);

        $foundCart = $this->repository->findById($cartId);

        $this->assertNotNull($foundCart);
        $this->assertTrue($foundCart->id()->equals($cartId));
        $this->assertTrue($foundCart->isAnonymous());
        $this->assertTrue($foundCart->isEmpty());
    }

    public function testItSavesAndFindsCartWithUser(): void
    {
        $cartId = CartId::generate();
        $userId = UserId::generate();
        $cart = Cart::create($cartId, $userId);

        $this->repository->save($cart);

        $foundCart = $this->repository->findById($cartId);

        $this->assertNotNull($foundCart);
        $this->assertTrue($foundCart->id()->equals($cartId));
        $this->assertFalse($foundCart->isAnonymous());
        $this->assertNotNull($foundCart->userId());
        $this->assertTrue($foundCart->userId()->equals($userId));
    }

    public function testItReturnsNullWhenCartNotFound(): void
    {
        $cartId = CartId::generate();

        $foundCart = $this->repository->findById($cartId);

        $this->assertNull($foundCart);
    }

    public function testItPreservesCartDates(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);
        $originalCreatedAt = $cart->createdAt();
        $originalExpiresAt = $cart->expiresAt();

        $this->repository->save($cart);

        $foundCart = $this->repository->findById($cartId);

        $this->assertNotNull($foundCart);
        $this->assertEquals(
            $originalCreatedAt->getTimestamp(),
            $foundCart->createdAt()->getTimestamp()
        );
        $this->assertEquals(
            $originalExpiresAt->getTimestamp(),
            $foundCart->expiresAt()->getTimestamp()
        );
    }

    public function testItSavesAndFindsCartWithOneItem(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        $productId = ProductId::generate();
        $productName = ProductName::fromString('Siroko Cycling Glasses');
        $unitPrice = Money::fromCents(9999, 'EUR');
        $quantity = Quantity::fromInt(2);

        $cart->addItem($productId, $productName, $unitPrice, $quantity);

        $this->repository->save($cart);

        $foundCart = $this->repository->findById($cartId);

        $this->assertNotNull($foundCart);
        $this->assertFalse($foundCart->isEmpty());
        $this->assertCount(1, $foundCart->items());

        $items = $foundCart->items();
        $item = $items[0];

        $this->assertTrue($item->productId()->equals($productId));
        $this->assertEquals('Siroko Cycling Glasses', $item->name()->value());
        $this->assertEquals(9999, $item->unitPrice()->amountInCents());
        $this->assertEquals('EUR', $item->unitPrice()->currency());
        $this->assertEquals(2, $item->quantity()->value());
        $this->assertInstanceOf(\DateTimeImmutable::class, $item->createdAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $item->updatedAt());
    }

    public function testItSavesAndFindsCartWithMultipleItems(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        // Add first product
        $product1Id = ProductId::generate();
        $product1Name = ProductName::fromString('Siroko Sunglasses');
        $cart->addItem($product1Id, $product1Name, Money::fromCents(5999, 'EUR'), Quantity::fromInt(1));

        // Add second product
        $product2Id = ProductId::generate();
        $product2Name = ProductName::fromString('Siroko Jersey');
        $cart->addItem($product2Id, $product2Name, Money::fromCents(7999, 'EUR'), Quantity::fromInt(2));

        $this->repository->save($cart);

        $foundCart = $this->repository->findById($cartId);

        $this->assertNotNull($foundCart);
        $this->assertCount(2, $foundCart->items());
        $this->assertEquals(3, $foundCart->totalItems());
    }

    public function testItUpdatesCartItemQuantity(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        $productId = ProductId::generate();
        $productName = ProductName::fromString('Siroko Helmet');
        $cart->addItem($productId, $productName, Money::fromCents(12999, 'EUR'), Quantity::fromInt(1));

        $this->repository->save($cart);

        // Reload cart and add same product again to increase quantity
        $reloadedCart = $this->repository->findById($cartId);
        $this->assertNotNull($reloadedCart);

        $reloadedCart->addItem($productId, $productName, Money::fromCents(12999, 'EUR'), Quantity::fromInt(2));

        $this->repository->save($reloadedCart);

        $foundCart = $this->repository->findById($cartId);

        $this->assertNotNull($foundCart);
        $this->assertCount(1, $foundCart->items());

        $items = $foundCart->items();
        $this->assertEquals(3, $items[0]->quantity()->value());
    }

    public function testItCalculatesCartTotal(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        // Product 1: 59.99 EUR x 2 = 119.98 EUR
        $cart->addItem(
            ProductId::generate(),
            ProductName::fromString('Product 1'),
            Money::fromCents(5999, 'EUR'),
            Quantity::fromInt(2)
        );

        // Product 2: 79.99 EUR x 1 = 79.99 EUR
        $cart->addItem(
            ProductId::generate(),
            ProductName::fromString('Product 2'),
            Money::fromCents(7999, 'EUR'),
            Quantity::fromInt(1)
        );

        $this->repository->save($cart);

        $foundCart = $this->repository->findById($cartId);

        $this->assertNotNull($foundCart);

        $total = $foundCart->total();
        // Total: 119.98 + 79.99 = 199.97 EUR (19997 cents)
        $this->assertEquals(19997, $total->amountInCents());
        $this->assertEquals('EUR', $total->currency());
    }

    public function testItPreservesItemTimestamps(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        $productId = ProductId::generate();
        $productName = ProductName::fromString('Test Product');
        $cart->addItem($productId, $productName, Money::fromCents(1000, 'EUR'), Quantity::fromInt(1));

        $this->repository->save($cart);

        $foundCart = $this->repository->findById($cartId);

        $this->assertNotNull($foundCart);
        $items = $foundCart->items();
        $this->assertCount(1, $items);

        $item = $items[0];
        $this->assertInstanceOf(\DateTimeImmutable::class, $item->createdAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $item->updatedAt());
        $this->assertEquals(
            $item->createdAt()->getTimestamp(),
            $item->updatedAt()->getTimestamp()
        );
    }

    public function testItUpdatesCartItemQuantityCorrectly(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId, UserId::generate());

        // Add an item first
        $productId = ProductId::generate();
        $productName = ProductName::fromString('Test Product');
        $cart->addItem($productId, $productName, Money::fromCents(2999, 'EUR'), Quantity::fromInt(2));

        $this->repository->save($cart);

        // Find the cart and get the item ID
        $foundCart = $this->repository->findById($cartId);
        $this->assertNotNull($foundCart);

        $items = $foundCart->items();
        $this->assertCount(1, $items);
        $cartItemId = $items[0]->id();

        // Store original timestamps
        $originalCreatedAt = $items[0]->createdAt();
        $originalUpdatedAt = $items[0]->updatedAt();

        // Add a small delay to ensure timestamp difference
        usleep(1000000); // 1 second

        // Update the quantity using the domain method
        $foundCart->updateItemQuantity($cartItemId, Quantity::fromInt(5));

        $this->repository->save($foundCart);

        // Verify the update persisted correctly
        $updatedCart = $this->repository->findById($cartId);
        $this->assertNotNull($updatedCart);

        $updatedItems = $updatedCart->items();
        $this->assertCount(1, $updatedItems);

        $updatedItem = $updatedItems[0];
        $this->assertEquals(5, $updatedItem->quantity()->value());

        // Verify timestamps - createdAt should be same, updatedAt should be different
        $this->assertEquals(
            $originalCreatedAt->getTimestamp(),
            $updatedItem->createdAt()->getTimestamp()
        );
        $this->assertGreaterThan(
            $originalUpdatedAt->getTimestamp(),
            $updatedItem->updatedAt()->getTimestamp()
        );
    }

    public function testItRemovesCartItemCorrectly(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId, UserId::generate());

        // Add two items first
        $product1Id = ProductId::generate();
        $product1Name = ProductName::fromString('Product 1');
        $cart->addItem($product1Id, $product1Name, Money::fromCents(2999, 'EUR'), Quantity::fromInt(2));

        $product2Id = ProductId::generate();
        $product2Name = ProductName::fromString('Product 2');
        $cart->addItem($product2Id, $product2Name, Money::fromCents(1999, 'EUR'), Quantity::fromInt(1));

        $this->repository->save($cart);

        // Find the cart and get the first item ID to remove
        $foundCart = $this->repository->findById($cartId);
        $this->assertNotNull($foundCart);

        $items = $foundCart->items();
        $this->assertCount(2, $items);
        $cartItemToRemoveId = $items[0]->id();

        // Remove the item using the domain method
        $foundCart->removeItem($cartItemToRemoveId);

        $this->repository->save($foundCart);

        // Verify the item was removed correctly
        $updatedCart = $this->repository->findById($cartId);
        $this->assertNotNull($updatedCart);

        $remainingItems = $updatedCart->items();
        $this->assertCount(1, $remainingItems);

        // Verify the remaining item is the second one
        $remainingItem = $remainingItems[0];
        $this->assertTrue($remainingItem->productId()->equals($product2Id));
        $this->assertEquals('Product 2', $remainingItem->name()->value());
        $this->assertEquals(1999, $remainingItem->unitPrice()->amountInCents());
        $this->assertEquals(1, $remainingItem->quantity()->value());

        // Verify cart total updated correctly
        $this->assertEquals(1999, $updatedCart->total()->amountInCents());
    }

    public function testItRemovesAllItemsFromCart(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        // Add one item
        $productId = ProductId::generate();
        $productName = ProductName::fromString('Single Product');
        $cart->addItem($productId, $productName, Money::fromCents(5999, 'EUR'), Quantity::fromInt(1));

        $this->repository->save($cart);

        // Find the cart and remove the only item
        $foundCart = $this->repository->findById($cartId);
        $this->assertNotNull($foundCart);

        $items = $foundCart->items();
        $this->assertCount(1, $items);
        $cartItemId = $items[0]->id();

        $foundCart->removeItem($cartItemId);

        $this->repository->save($foundCart);

        // Verify the cart is now empty
        $emptyCart = $this->repository->findById($cartId);
        $this->assertNotNull($emptyCart);
        $this->assertTrue($emptyCart->isEmpty());
        $this->assertCount(0, $emptyCart->items());
        $this->assertEquals(0, $emptyCart->total()->amountInCents());
    }

    public function testItRetrievesCartWithCompleteDetails(): void
    {
        $cartId = CartId::generate();
        $userId = UserId::generate();
        $cart = Cart::create($cartId, $userId);

        // Add multiple items with different prices and quantities
        $product1Id = ProductId::generate();
        $cart->addItem(
            $product1Id,
            ProductName::fromString('Premium Sunglasses'),
            Money::fromCents(12999, 'EUR'),
            Quantity::fromInt(1)
        );

        $product2Id = ProductId::generate();
        $cart->addItem(
            $product2Id,
            ProductName::fromString('Sports Helmet'),
            Money::fromCents(8999, 'EUR'),
            Quantity::fromInt(2)
        );

        $this->repository->save($cart);

        // Retrieve cart and verify all details
        $retrievedCart = $this->repository->findById($cartId);

        $this->assertNotNull($retrievedCart);
        $this->assertTrue($retrievedCart->id()->equals($cartId));
        $this->assertFalse($retrievedCart->isAnonymous());
        $this->assertNotNull($retrievedCart->userId());
        $this->assertTrue($retrievedCart->userId()->equals($userId));

        // Verify items
        $this->assertCount(2, $retrievedCart->items());
        $this->assertEquals(3, $retrievedCart->totalItems()); // 1 + 2

        // Verify total calculation: 129.99 + (89.99 * 2) = 309.97
        $this->assertEquals(30997, $retrievedCart->total()->amountInCents());
        $this->assertEquals('EUR', $retrievedCart->total()->currency());

        // Verify individual items
        $items = $retrievedCart->items();
        $foundPremiumSunglasses = false;
        $foundSportsHelmet = false;

        foreach ($items as $item) {
            if ($item->productId()->equals($product1Id)) {
                $foundPremiumSunglasses = true;
                $this->assertEquals('Premium Sunglasses', $item->name()->value());
                $this->assertEquals(12999, $item->unitPrice()->amountInCents());
                $this->assertEquals(1, $item->quantity()->value());
                $this->assertEquals(12999, $item->subtotal()->amountInCents());
            } elseif ($item->productId()->equals($product2Id)) {
                $foundSportsHelmet = true;
                $this->assertEquals('Sports Helmet', $item->name()->value());
                $this->assertEquals(8999, $item->unitPrice()->amountInCents());
                $this->assertEquals(2, $item->quantity()->value());
                $this->assertEquals(17998, $item->subtotal()->amountInCents());
            }
        }

        $this->assertTrue($foundPremiumSunglasses, 'Premium Sunglasses item not found');
        $this->assertTrue($foundSportsHelmet, 'Sports Helmet item not found');
    }

    public function testItRetrievesEmptyCartCorrectly(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        $this->repository->save($cart);

        $retrievedCart = $this->repository->findById($cartId);

        $this->assertNotNull($retrievedCart);
        $this->assertTrue($retrievedCart->id()->equals($cartId));
        $this->assertTrue($retrievedCart->isAnonymous());
        $this->assertTrue($retrievedCart->isEmpty());
        $this->assertCount(0, $retrievedCart->items());
        $this->assertEquals(0, $retrievedCart->total()->amountInCents());
        $this->assertEquals(0, $retrievedCart->totalItems());
    }

    public function testItReturnsNullForExpiredCart(): void
    {
        $cartId = CartId::generate();
        $userId = UserId::generate();

        // Create a cart with past expiration using reconstruct
        $expiredDate = new \DateTimeImmutable('-1 day');
        $cart = Cart::reconstruct(
            $cartId,
            $userId,
            new \DateTimeImmutable('-8 days'),
            $expiredDate,
            []
        );

        // Save the expired cart directly to database bypassing domain rules
        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        // Try to retrieve the expired cart - should return null
        $retrievedCart = $this->repository->findById($cartId);

        $this->assertNull($retrievedCart);
    }
}
