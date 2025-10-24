<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Checkout\Aggregate\Order;
use App\Domain\Checkout\Entity\OrderItem;
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

        $managedOrder = null;

        if (0 === (int) $exists) {
            // Order doesn't exist, persist it first
            $this->entityManager->persist($order);
            $this->entityManager->flush(); // Flush to make it managed
            $managedOrder = $order; // Now it's managed
        } else {
            // Order exists, get managed instance by clearing first and then finding
            $this->entityManager->clear(); // Clear identity map to avoid conflicts
            $managedOrder = $this->entityManager->find(Order::class, $order->id());
            if (null === $managedOrder) {
                throw new \RuntimeException('Order should exist but was not found');
            }
        }

        // Get current item IDs from the domain order
        $currentItemIds = array_map(
            fn ($item) => $item->id(),
            $order->items()
        );

        // Remove items that are no longer in the order
        if (!empty($currentItemIds)) {
            $this->entityManager
                ->createQuery('DELETE FROM '.OrderItem::class.' oi WHERE oi.order = :orderId AND oi.id NOT IN (:currentIds)')
                ->setParameter('orderId', $managedOrder->id())
                ->setParameter('currentIds', $currentItemIds)
                ->execute();
        } else {
            // If order is empty, remove all items
            $this->entityManager
                ->createQuery('DELETE FROM '.OrderItem::class.' oi WHERE oi.order = :orderId')
                ->setParameter('orderId', $managedOrder->id())
                ->execute();
        }

        // Persist new items and update existing ones with managed Order reference
        foreach ($order->items() as $item) {
            /** @var OrderItem|null $existingItem */
            $existingItem = $this->entityManager
                ->createQuery('SELECT oi FROM '.OrderItem::class.' oi WHERE oi.id = :itemId')
                ->setParameter('itemId', $item->id())
                ->getOneOrNullResult();

            if (null === $existingItem) {
                // Create new item with managed order reference
                $newItem = OrderItem::reconstruct(
                    $item->id(),
                    $managedOrder,
                    $item->productId(),
                    $item->name(),
                    $item->unitPrice(),
                    $item->quantity(),
                    $item->createdAt(),
                    $item->updatedAt()
                );
                $this->entityManager->persist($newItem);
            }
        }

        // Update the managed order's state
        $managedOrder->captureTotal($order->total());
        $managedOrder->changeStatus($order->status());

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

        // Load items for this order
        /** @var array<OrderItem> $orderItems */
        $orderItems = $this->entityManager
            ->createQuery('SELECT oi FROM '.OrderItem::class.' oi WHERE oi.order = :orderId ORDER BY oi.createdAt')
            ->setParameter('orderId', $orderId)
            ->getResult();

        // Convert to associative array indexed by item ID
        $items = [];
        foreach ($orderItems as $item) {
            $items[$item->id()->value()] = $item;
        }

        return Order::reconstruct(
            $orderData['id'],
            $orderData['userId'],
            $orderData['status'],
            $orderData['total'],
            $orderData['createdAt'],
            $orderData['updatedAt'],
            $items
        );
    }
}
