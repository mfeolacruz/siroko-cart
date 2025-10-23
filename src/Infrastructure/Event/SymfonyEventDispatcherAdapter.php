<?php

declare(strict_types=1);

namespace App\Infrastructure\Event;

use App\Application\Shared\EventDispatcherInterface as ApplicationEventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

final readonly class SymfonyEventDispatcherAdapter implements ApplicationEventDispatcherInterface
{
    public function __construct(
        private SymfonyEventDispatcherInterface $symfonyDispatcher,
    ) {
    }

    public function dispatch(array $events): void
    {
        foreach ($events as $event) {
            $this->symfonyDispatcher->dispatch($event, $event->eventName());
        }
    }
}
