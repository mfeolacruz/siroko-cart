<?php

declare(strict_types=1);

namespace App\Domain\Cart\Aggregate;

use App\Domain\Cart\Entity\CartItem;
use App\Domain\Cart\Event\CartCreated;
use App\Domain\Cart\Event\CartItemAdded;
use App\Domain\Cart\Event\CartItemQuantityUpdated;
use App\Domain\Cart\Event\CartItemRemoved;
use App\Domain\Cart\Exception\CartItemNotFoundException;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Shared\Aggregate\AggregateRoot;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Shared\ValueObject\ProductId;
use App\Domain\Shared\ValueObject\ProductName;
use App\Domain\Shared\ValueObject\Quantity;
use App\Domain\Shared\ValueObject\UserId;

class Cart extends AggregateRoot
{
    private const DEFAULT_EXPIRATION_DAYS = 7;
    private const DEFAULT_CURRENCY = 'EUR';

    /** @var array<string, CartItem> */
    private array $items = [];

    private function __construct(
        private readonly CartId $id,
        private readonly ?UserId $userId,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $expiresAt,
    ) {
    }

    public static function create(CartId $id, ?UserId $userId = null): self
    {
        $createdAt = new \DateTimeImmutable();
        $expiresAt = $createdAt->modify('+'.self::DEFAULT_EXPIRATION_DAYS.' days');

        $cart = new self(
            $id,
            $userId,
            $createdAt,
            $expiresAt
        );

        $cart->record(new CartCreated($id, $userId, $createdAt));

        return $cart;
    }

    public function id(): CartId
    {
        return $this->id;
    }

    public function userId(): ?UserId
    {
        return $this->userId;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function expiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function addItem(
        ProductId $productId,
        ProductName $name,
        Money $unitPrice,
        Quantity $quantity,
    ): void {
        $productIdValue = $productId->value();

        if (isset($this->items[$productIdValue])) {
            $this->items[$productIdValue]->increaseQuantity($quantity);
            $cartItemId = $this->items[$productIdValue]->id();
        } else {
            $cartItemId = CartItemId::generate();
            $this->items[$productIdValue] = CartItem::create(
                $cartItemId,
                $this,
                $productId,
                $name,
                $unitPrice,
                $quantity
            );
        }

        $this->record(
            CartItemAdded::create(
                $this->id,
                $cartItemId,
                $productId,
                $name,
                $unitPrice,
                $quantity,
                new \DateTimeImmutable()
            )
        );
    }

    /**
     * @return array<CartItem>
     */
    public function items(): array
    {
        return array_values($this->items);
    }

    public function total(): Money
    {
        if ($this->isEmpty()) {
            return Money::fromCents(0, self::DEFAULT_CURRENCY);
        }

        $total = Money::fromCents(0, self::DEFAULT_CURRENCY);

        foreach ($this->items as $item) {
            $total = $total->add($item->subtotal());
        }

        return $total;
    }

    public function isEmpty(): bool
    {
        return 0 === count($this->items);
    }

    public function isAnonymous(): bool
    {
        return null === $this->userId;
    }

    /**
     * Reconstruct Cart from persistence (used by infrastructure).
     *
     * @param array<CartItem> $items
     */
    public static function reconstruct(
        CartId $id,
        ?UserId $userId,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $expiresAt,
        array $items,
    ): self {
        $cart = new self($id, $userId, $createdAt, $expiresAt);

        // Rebuild internal items structure indexed by productId
        foreach ($items as $item) {
            $cart->items[$item->productId()->value()] = $item;
        }

        return $cart;
    }

    public function totalItems(): int
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->quantity()->value();
        }

        return $total;
    }

    public function findItemById(CartItemId $cartItemId): ?CartItem
    {
        foreach ($this->items as $item) {
            if ($item->id()->equals($cartItemId)) {
                return $item;
            }
        }

        return null;
    }

    public function updateItemQuantity(CartItemId $cartItemId, Quantity $newQuantity): void
    {
        $item = $this->findItemById($cartItemId);

        if (null === $item) {
            throw CartItemNotFoundException::withId($cartItemId);
        }

        $previousQuantity = $item->quantity();
        $item->updateQuantity($newQuantity);

        $this->record(
            CartItemQuantityUpdated::create(
                $this->id,
                $cartItemId,
                $previousQuantity,
                $newQuantity,
                new \DateTimeImmutable()
            )
        );
    }

    public function removeItem(CartItemId $cartItemId): void
    {
        $itemToRemove = $this->findItemById($cartItemId);

        if (null === $itemToRemove) {
            throw CartItemNotFoundException::withId($cartItemId);
        }

        // Remove the item from internal array by finding its array key
        foreach ($this->items as $productIdKey => $item) {
            if ($item->id()->equals($cartItemId)) {
                unset($this->items[$productIdKey]);
                break;
            }
        }

        // Record domain event
        $this->record(
            CartItemRemoved::create(
                $this->id,
                $cartItemId,
                $itemToRemove->productId()
            )
        );
    }

    public function forceExpiration(): void
    {
        $this->expiresAt = new \DateTimeImmutable();
    }

    public function isExpired(): bool
    {
        return $this->expiresAt <= new \DateTimeImmutable();
    }
}
