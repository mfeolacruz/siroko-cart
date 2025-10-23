<?php

declare(strict_types=1);

namespace App\Domain\Cart\Aggregate;

use App\Domain\Cart\Event\CartCreated;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\UserId;
use App\Domain\Shared\Event\DomainEvent;

final class Cart
{
    /** @var array<empty, empty> */
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

    /**
     * @return array<empty, empty>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function isAnonymous(): bool
    {
        return null === $this->userId;
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
