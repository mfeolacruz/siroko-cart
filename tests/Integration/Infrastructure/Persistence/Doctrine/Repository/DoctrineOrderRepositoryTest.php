<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Checkout\Aggregate\Order;
use App\Domain\Checkout\Repository\OrderRepositoryInterface;
use App\Domain\Checkout\ValueObject\OrderId;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Shared\ValueObject\UserId;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineOrderRepositoryTest extends KernelTestCase
{
    private OrderRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $repository = static::getContainer()->get(OrderRepositoryInterface::class);
        assert($repository instanceof OrderRepositoryInterface);
        $this->repository = $repository;
    }

    public function testItSavesAndFindsAnonymousOrder(): void
    {
        $orderId = OrderId::generate();
        $order = Order::create($orderId);

        $this->repository->save($order);

        $foundOrder = $this->repository->findById($orderId);

        $this->assertNotNull($foundOrder);
        $this->assertTrue($foundOrder->id()->equals($orderId));
        $this->assertNull($foundOrder->userId());
        $this->assertEquals(0, $foundOrder->total()->amountInCents());
    }

    public function testItSavesAndFindsOrderWithUser(): void
    {
        $orderId = OrderId::generate();
        $userId = UserId::generate();
        $order = Order::create($orderId, $userId);

        $this->repository->save($order);

        $foundOrder = $this->repository->findById($orderId);

        $this->assertNotNull($foundOrder);
        $this->assertTrue($foundOrder->id()->equals($orderId));
        $this->assertNotNull($foundOrder->userId());
        $this->assertTrue($foundOrder->userId()->equals($userId));
    }

    public function testItReturnsNullWhenOrderNotFound(): void
    {
        $orderId = OrderId::generate();

        $foundOrder = $this->repository->findById($orderId);

        $this->assertNull($foundOrder);
    }

    public function testItPreservesOrderDates(): void
    {
        $orderId = OrderId::generate();
        $order = Order::create($orderId);
        $originalCreatedAt = $order->createdAt();
        $originalUpdatedAt = $order->updatedAt();

        $this->repository->save($order);

        $foundOrder = $this->repository->findById($orderId);

        $this->assertNotNull($foundOrder);
        $this->assertEquals(
            $originalCreatedAt->getTimestamp(),
            $foundOrder->createdAt()->getTimestamp()
        );
        $this->assertEquals(
            $originalUpdatedAt->getTimestamp(),
            $foundOrder->updatedAt()->getTimestamp()
        );
    }

    public function testItCapturesOrderTotal(): void
    {
        $orderId = OrderId::generate();
        $order = Order::create($orderId);

        // Capture a total
        $total = Money::fromCents(15999, 'EUR');
        $order->captureTotal($total);

        $this->repository->save($order);

        $foundOrder = $this->repository->findById($orderId);

        $this->assertNotNull($foundOrder);
        $this->assertEquals(15999, $foundOrder->total()->amountInCents());
        $this->assertEquals('EUR', $foundOrder->total()->currency());
    }

    public function testItPreservesOrderStatus(): void
    {
        $orderId = OrderId::generate();
        $order = Order::create($orderId);

        $this->repository->save($order);

        $foundOrder = $this->repository->findById($orderId);

        $this->assertNotNull($foundOrder);
        $this->assertEquals('pending', $foundOrder->status()->value());
    }
}
