<?php

declare(strict_types=1);

namespace App\Domain\Shared\Event;

abstract readonly class DomainEvent
{
    private \DateTimeImmutable $occurredOn;

    public function __construct()
    {
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    abstract public function eventName(): string;

    /**
     * @return array<string, mixed>
     */
    abstract public function toPrimitives(): array;
}
