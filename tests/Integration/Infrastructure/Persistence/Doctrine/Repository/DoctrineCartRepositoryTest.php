<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
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
}
