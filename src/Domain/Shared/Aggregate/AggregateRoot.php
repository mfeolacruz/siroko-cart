<?php

declare(strict_types=1);

namespace App\Domain\Shared\Aggregate;

use App\Domain\Shared\Event\DomainEvent;

abstract class AggregateRoot
{
    /** @var array<int, DomainEvent> */
    private array $domainEvents = [];

    /**
     * @return array<DomainEvent>
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    protected function record(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }
}
