<?php

declare(strict_types=1);

namespace App\Application\Shared;

use App\Domain\Shared\Event\DomainEvent;

interface EventDispatcherInterface
{
    /**
     * @param array<int, DomainEvent> $events
     */
    public function dispatch(array $events): void;
}
