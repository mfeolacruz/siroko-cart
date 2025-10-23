<?php

declare(strict_types=1);

namespace App\Domain\Cart\Aggregate;

use App\Domain\Cart\Entity\CartItem;
use App\Domain\Cart\Event\CartCreated;
use App\Domain\Cart\Event\CartItemAdded;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Cart\ValueObject\Money;
use App\Domain\Cart\ValueObject\ProductId;
use App\Domain\Cart\ValueObject\ProductName;
use App\Domain\Cart\ValueObject\Quantity;
use App\Domain\Cart\ValueObject\UserId;
use App\Domain\Shared\Event\DomainEvent;

final class Cart
{
    /** @var array<string, CartItem> */
    private array $items = [];

    /** @var array<int, DomainEvent> */
    private array $domainEvents = [];

    private function __construct(
        private readonly CartId $id,
        private readonly ?UserId $userId,
        private readonly \DateTimeImmutable $createdAt,
        private readonly \DateTimeImmutable $expiresAt,
    ) {
    }

    public static function create(CartId $id, ?UserId $userId = null): self
    {
        $createdAt = new \DateTimeImmutable();
        $expiresAt = $createdAt->modify('+7 days');

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
            return Money::fromCents(0, 'EUR');
        }

        $total = Money::fromCents(0, 'EUR');

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

    /**
     * @return array<int, DomainEvent>
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    private function record(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }
}
