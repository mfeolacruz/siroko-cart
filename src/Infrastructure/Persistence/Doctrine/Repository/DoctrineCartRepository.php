<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Entity\CartItem;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineCartRepository implements CartRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Cart $cart): void
    {
        // Check if cart exists
        $exists = $this->entityManager
            ->createQuery('SELECT COUNT(c.id) FROM '.Cart::class.' c WHERE c.id = :cartId')
            ->setParameter('cartId', $cart->id())
            ->getSingleScalarResult();

        $managedCart = null;

        if (0 === (int) $exists) {
            // Cart doesn't exist, persist it first
            $this->entityManager->persist($cart);
            $this->entityManager->flush(); // Flush to make it managed
            $managedCart = $cart; // Now it's managed
        } else {
            // Cart exists, get managed instance by clearing first and then finding
            $this->entityManager->clear(); // Clear identity map to avoid conflicts
            $managedCart = $this->entityManager->find(Cart::class, $cart->id());
            if (null === $managedCart) {
                throw new \RuntimeException('Cart should exist but was not found');
            }
        }

        // Get current item IDs from the domain cart
        $currentItemIds = array_map(
            fn ($item) => $item->id(),
            $cart->items()
        );

        // Remove items that are no longer in the cart
        if (!empty($currentItemIds)) {
            $this->entityManager
                ->createQuery('DELETE FROM '.CartItem::class.' ci WHERE ci.cart = :cartId AND ci.id NOT IN (:currentIds)')
                ->setParameter('cartId', $managedCart->id())
                ->setParameter('currentIds', $currentItemIds)
                ->execute();
        } else {
            // If cart is empty, remove all items
            $this->entityManager
                ->createQuery('DELETE FROM '.CartItem::class.' ci WHERE ci.cart = :cartId')
                ->setParameter('cartId', $managedCart->id())
                ->execute();
        }

        // Persist new items and update existing ones with managed Cart reference
        foreach ($cart->items() as $item) {
            /** @var CartItem|null $existingItem */
            $existingItem = $this->entityManager
                ->createQuery('SELECT ci FROM '.CartItem::class.' ci WHERE ci.id = :itemId')
                ->setParameter('itemId', $item->id())
                ->getOneOrNullResult();

            if (null === $existingItem) {
                // Create new item with managed cart reference
                $newItem = CartItem::create(
                    $item->id(),
                    $managedCart,
                    $item->productId(),
                    $item->name(),
                    $item->unitPrice(),
                    $item->quantity()
                );
                $this->entityManager->persist($newItem);
            } else {
                // Update existing item to match current state
                $existingItem->updateQuantity($item->quantity());
            }
        }

        $this->entityManager->flush();
    }

    public function findById(CartId $cartId): ?Cart
    {
        // Query scalar data to avoid Identity Map collision - returns array, not managed entity
        /** @var array{id: CartId, userId: \App\Domain\Shared\ValueObject\UserId|null, createdAt: \DateTimeImmutable, expiresAt: \DateTimeImmutable}|null $cartData */
        $cartData = $this->entityManager
            ->createQuery('SELECT c.id, c.userId, c.createdAt, c.expiresAt FROM '.Cart::class.' c WHERE c.id = :cartId')
            ->setParameter('cartId', $cartId)
            ->getOneOrNullResult();

        if (null === $cartData) {
            return null;
        }

        /** @var array<CartItem> $items */
        $items = $this->entityManager
            ->createQuery('SELECT ci FROM '.CartItem::class.' ci WHERE ci.cart = :cartId')
            ->setParameter('cartId', $cartId)
            ->getResult();

        return Cart::reconstruct(
            $cartData['id'],
            $cartData['userId'],
            $cartData['createdAt'],
            $cartData['expiresAt'],
            $items
        );
    }
}
