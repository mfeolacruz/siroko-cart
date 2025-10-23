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
        // Persist cart if new
        if (null === $this->entityManager->find(Cart::class, $cart->id())) {
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }

        // Persist all items (new items will have cart reference already set)
        foreach ($cart->items() as $item) {
            if (!$this->entityManager->contains($item)) {
                $this->entityManager->persist($item);
            }
        }

        $this->entityManager->flush();
    }

    public function findById(CartId $cartId): ?Cart
    {
        $cartData = $this->entityManager->find(Cart::class, $cartId);

        if (null === $cartData) {
            return null;
        }

        /** @var array<CartItem> $items */
        $items = $this->entityManager
            ->createQuery('SELECT ci FROM '.CartItem::class.' ci WHERE ci.cart = :cartId')
            ->setParameter('cartId', $cartId)
            ->getResult();

        return Cart::reconstruct(
            $cartData->id(),
            $cartData->userId(),
            $cartData->createdAt(),
            $cartData->expiresAt(),
            $items
        );
    }
}
