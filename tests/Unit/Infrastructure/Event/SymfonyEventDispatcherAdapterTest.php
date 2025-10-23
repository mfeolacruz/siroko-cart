<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Event;

use App\Domain\Cart\Event\CartCreated;
use App\Domain\Cart\ValueObject\CartId;
use App\Infrastructure\Event\SymfonyEventDispatcherAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

final class SymfonyEventDispatcherAdapterTest extends TestCase
{
    public function testItDispatchesDomainEventsToSymfony(): void
    {
        $symfonyDispatcher = $this->createMock(SymfonyEventDispatcherInterface::class);

        $cartId = CartId::generate();
        $event = new CartCreated($cartId, null, new \DateTimeImmutable());

        $symfonyDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->identicalTo($event),
                $this->equalTo('cart.created')
            );

        $adapter = new SymfonyEventDispatcherAdapter($symfonyDispatcher);
        $adapter->dispatch([$event]);
    }

    public function testItDispatchesMultipleEvents(): void
    {
        $symfonyDispatcher = $this->createMock(SymfonyEventDispatcherInterface::class);

        $event1 = new CartCreated(CartId::generate(), null, new \DateTimeImmutable());
        $event2 = new CartCreated(CartId::generate(), null, new \DateTimeImmutable());

        $symfonyDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch');

        $adapter = new SymfonyEventDispatcherAdapter($symfonyDispatcher);
        $adapter->dispatch([$event1, $event2]);
    }

    public function testItHandlesEmptyEventArray(): void
    {
        $symfonyDispatcher = $this->createMock(SymfonyEventDispatcherInterface::class);

        $symfonyDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $adapter = new SymfonyEventDispatcherAdapter($symfonyDispatcher);
        $adapter->dispatch([]);
    }
}
