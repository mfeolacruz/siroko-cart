<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Cart\Command;

use App\Application\Cart\Command\AddCartItemCommand;
use App\Application\Cart\Command\AddCartItemCommandHandler;
use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Exception\CartNotFoundException;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Shared\Event\EventDispatcherInterface;
use App\Domain\Shared\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class AddCartItemCommandHandlerTest extends TestCase
{
    /** @var CartRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private CartRepositoryInterface $cartRepository;

    /** @var EventDispatcherInterface&\PHPUnit\Framework\MockObject\MockObject */
    private EventDispatcherInterface $eventDispatcher;
    private AddCartItemCommandHandler $handler;

    protected function setUp(): void
    {
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->handler = new AddCartItemCommandHandler(
            $this->cartRepository,
            $this->eventDispatcher
        );
    }

    public function testHandlerAddsItemToCart(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId, UserId::generate());

        $command = new AddCartItemCommand(
            $cartId->value(),
            '550e8400-e29b-41d4-a716-446655440001',
            'Gafas Siroko Tech K3',
            49.99,
            'EUR',
            2
        );

        $this->cartRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->callback(fn ($id) => $id->equals($cartId)))
            ->willReturn($cart);

        $this->cartRepository
            ->expects($this->once())
            ->method('save')
            ->with($cart);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch');

        $this->handler->handle($command);

        $this->assertCount(1, $cart->items());
    }

    public function testHandlerThrowsExceptionWhenCartNotFound(): void
    {
        $cartId = CartId::generate();

        $command = new AddCartItemCommand(
            $cartId->value(),
            '550e8400-e29b-41d4-a716-446655440001',
            'Gafas Siroko',
            49.99,
            'EUR',
            1
        );

        $this->cartRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $this->expectException(CartNotFoundException::class);

        $this->handler->handle($command);
    }
}
