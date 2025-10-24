<?php

declare(strict_types=1);

namespace App\Domain\Cart\Event;

use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Shared\Event\DomainEvent;
use App\Domain\Shared\ValueObject\UserId;

final readonly class CartCreated extends DomainEvent
{
    public function __construct(
        private CartId $cartId,
        private ?UserId $userId,
        private \DateTimeImmutable $createdAt,
    ) {
        parent::__construct();
    }

    public function cartId(): CartId
    {
        return $this->cartId;
    }

    public function userId(): ?UserId
    {
        return $this->userId;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function eventName(): string
    {
        return 'cart.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function toPrimitives(): array
    {
        return [
            'cart_id' => $this->cartId->value(),
            'user_id' => $this->userId?->value(),
            'created_at' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'occurred_on' => $this->occurredOn()->format(\DateTimeInterface::ATOM),
        ];
    }
}
