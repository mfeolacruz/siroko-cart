<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Cart\Command;

use App\Application\Cart\Command\CreateCartCommand;
use App\Application\Cart\Command\CreateCartCommandHandler;
use App\Application\Shared\EventDispatcherInterface;
use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Event\CartCreated;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use PHPUnit\Framework\TestCase;

final class CreateCartCommandHandlerTest extends TestCase
{
    public function testItCreatesAnonymousCart(): void
    {
        $repository = $this->createMock(CartRepositoryInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Cart $cart) {
                return $cart->isAnonymous() && $cart->isEmpty();
            }));

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (array $events) {
                return 1 === count($events) && $events[0] instanceof CartCreated;
            }));

        $handler = new CreateCartCommandHandler($repository, $eventDispatcher);
        $command = new CreateCartCommand(null);

        $cartId = $handler->handle($command);

        $this->assertInstanceOf(CartId::class, $cartId);
    }

    public function testItCreatesCartForUser(): void
    {
        $userId = '550e8400-e29b-41d4-a716-446655440000';

        $repository = $this->createMock(CartRepositoryInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Cart $cart) {
                return !$cart->isAnonymous() && $cart->isEmpty();
            }));

        $eventDispatcher->expects($this->once())
            ->method('dispatch');

        $handler = new CreateCartCommandHandler($repository, $eventDispatcher);
        $command = new CreateCartCommand($userId);

        $cartId = $handler->handle($command);

        $this->assertInstanceOf(CartId::class, $cartId);
    }
}
