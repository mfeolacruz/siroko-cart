<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\Money;
use App\Domain\Cart\ValueObject\ProductId;
use App\Domain\Cart\ValueObject\ProductName;
use App\Domain\Cart\ValueObject\Quantity;
use App\Domain\Cart\ValueObject\UserId;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineCartRepositoryTest extends KernelTestCase
{
    private CartRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $repository = static::getContainer()->get(CartRepositoryInterface::class);
        assert($repository instanceof CartRepositoryInterface);
        $this->repository = $repository;
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
}
