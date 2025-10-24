<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Entity\CartItem;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\CartItemId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineCartRepository implements CartRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Cart $cart): void
    {
        $managedCart = $this->getOrCreateManagedCart($cart);
        $this->synchronizeCartItems($cart, $managedCart);
        $this->updateCartFields($cart, $managedCart);
        $this->entityManager->flush();
    }

    private function getOrCreateManagedCart(Cart $cart): Cart
    {
        if ($this->cartExists($cart->id())) {
            return $this->getManagedCart($cart);
        }

        return $this->persistNewCart($cart);
    }

    private function cartExists(CartId $cartId): bool
    {
        $exists = $this->entityManager
            ->createQuery('SELECT COUNT(c.id) FROM '.Cart::class.' c WHERE c.id = :cartId')
            ->setParameter('cartId', $cartId)
            ->getSingleScalarResult();

        return (int) $exists > 0;
    }

    private function persistNewCart(Cart $cart): Cart
    {
        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        return $cart;
    }

    private function getManagedCart(Cart $cart): Cart
    {
        $this->entityManager->clear();
        $managedCart = $this->entityManager->find(Cart::class, $cart->id());

        if (null === $managedCart) {
            throw new \RuntimeException('Cart should exist but was not found');
        }

        return $managedCart;
    }

    private function synchronizeCartItems(Cart $domainCart, Cart $managedCart): void
    {
        $this->removeStaleCartItems($domainCart, $managedCart);
        $this->persistOrUpdateCartItems($domainCart, $managedCart);
    }

    private function removeStaleCartItems(Cart $domainCart, Cart $managedCart): void
    {
        $currentItemIds = array_map(fn ($item) => $item->id(), $domainCart->items());

        if (!empty($currentItemIds)) {
            $this->entityManager
                ->createQuery('DELETE FROM '.CartItem::class.' ci WHERE ci.cart = :cartId AND ci.id NOT IN (:currentIds)')
                ->setParameter('cartId', $managedCart->id())
                ->setParameter('currentIds', $currentItemIds)
                ->execute();
        } else {
            $this->entityManager
                ->createQuery('DELETE FROM '.CartItem::class.' ci WHERE ci.cart = :cartId')
                ->setParameter('cartId', $managedCart->id())
                ->execute();
        }
    }

    private function persistOrUpdateCartItems(Cart $domainCart, Cart $managedCart): void
    {
        foreach ($domainCart->items() as $item) {
            $existingItem = $this->findExistingCartItem($item->id());

            if (null === $existingItem) {
                $this->persistNewCartItem($item, $managedCart);
            } else {
                $existingItem->updateQuantity($item->quantity());
            }
        }
    }

    private function findExistingCartItem(CartItemId $itemId): ?CartItem
    {
        /** @var CartItem|null $existingItem */
        $existingItem = $this->entityManager
            ->createQuery('SELECT ci FROM '.CartItem::class.' ci WHERE ci.id = :itemId')
            ->setParameter('itemId', $itemId)
            ->getOneOrNullResult();

        return $existingItem;
    }

    private function persistNewCartItem(CartItem $item, Cart $managedCart): void
    {
        $newItem = CartItem::create(
            $item->id(),
            $managedCart,
            $item->productId(),
            $item->name(),
            $item->unitPrice(),
            $item->quantity()
        );
        $this->entityManager->persist($newItem);
    }

    private function updateCartFields(Cart $domainCart, Cart $managedCart): void
    {
        if ($domainCart->expiresAt() !== $managedCart->expiresAt()) {
            $this->entityManager
                ->createQuery('UPDATE '.Cart::class.' c SET c.expiresAt = :expiresAt WHERE c.id = :cartId')
                ->setParameter('expiresAt', $domainCart->expiresAt())
                ->setParameter('cartId', $domainCart->id())
                ->execute();
        }
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

        // Check if cart is expired - if so, treat as non-existent
        if ($cartData['expiresAt'] <= new \DateTimeImmutable()) {
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
