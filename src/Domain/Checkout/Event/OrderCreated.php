<?php

declare(strict_types=1);

namespace App\Domain\Checkout\Event;

use App\Domain\Checkout\ValueObject\OrderId;
use App\Domain\Shared\Event\DomainEvent;
use App\Domain\Shared\ValueObject\UserId;

final readonly class OrderCreated extends DomainEvent
{
    public function __construct(
        private OrderId $orderId,
        private ?UserId $userId,
        private \DateTimeImmutable $createdAt,
    ) {
        parent::__construct();
    }

    public function orderId(): OrderId
    {
        return $this->orderId;
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
        return 'order.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function toPrimitives(): array
    {
        return [
            'order_id' => $this->orderId->value(),
            'user_id' => $this->userId?->value(),
            'created_at' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'occurred_on' => $this->occurredOn()->format(\DateTimeInterface::ATOM),
        ];
    }
}
