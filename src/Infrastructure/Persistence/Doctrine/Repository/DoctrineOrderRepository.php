<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Checkout\Aggregate\Order;
use App\Domain\Checkout\Repository\OrderRepositoryInterface;
use App\Domain\Checkout\ValueObject\OrderId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineOrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Order $order): void
    {
        // Check if order exists
        $exists = $this->entityManager
            ->createQuery('SELECT COUNT(o.id) FROM '.Order::class.' o WHERE o.id = :orderId')
            ->setParameter('orderId', $order->id())
            ->getSingleScalarResult();

        if (0 === (int) $exists) {
            // Order doesn't exist, persist it
            $this->entityManager->persist($order);
        } else {
            // Order exists, get managed instance and update it
            $this->entityManager->clear();
            $managedOrder = $this->entityManager->find(Order::class, $order->id());
            if (null === $managedOrder) {
                throw new \RuntimeException('Order should exist but was not found');
            }

            // Update the managed order's state
            $managedOrder->captureTotal($order->total());
            $managedOrder->changeStatus($order->status());
        }

        $this->entityManager->flush();
    }

    public function findById(OrderId $orderId): ?Order
    {
        // Query scalar data to avoid Identity Map collision
        /** @var array{id: OrderId, userId: \App\Domain\Shared\ValueObject\UserId|null, status: \App\Domain\Checkout\ValueObject\OrderStatus, total: \App\Domain\Shared\ValueObject\Money, createdAt: \DateTimeImmutable, updatedAt: \DateTimeImmutable}|null $orderData */
        $orderData = $this->entityManager
            ->createQuery('SELECT o.id, o.userId, o.status, o.total, o.createdAt, o.updatedAt FROM '.Order::class.' o WHERE o.id = :orderId')
            ->setParameter('orderId', $orderId)
            ->getOneOrNullResult();

        if (null === $orderData) {
            return null;
        }

        return Order::reconstruct(
            $orderData['id'],
            $orderData['userId'],
            $orderData['status'],
            $orderData['total'],
            $orderData['createdAt'],
            $orderData['updatedAt'],
            []
        );
    }
}
