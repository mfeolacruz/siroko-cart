<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Cart\Command;

use App\Application\Cart\Command\RemoveCartItemCommand;
use App\Application\Cart\Command\RemoveCartItemCommandHandler;
use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Exception\CartItemNotFoundException;
use App\Domain\Cart\Exception\CartNotFoundException;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Cart\ValueObject\Money;
use App\Domain\Cart\ValueObject\ProductId;
use App\Domain\Cart\ValueObject\ProductName;
use App\Domain\Cart\ValueObject\Quantity;
use App\Domain\Cart\ValueObject\UserId;
use App\Domain\Shared\Event\EventDispatcherInterface;
use PHPUnit\Framework\TestCase;

final class RemoveCartItemCommandHandlerTest extends TestCase
{
    /** @var CartRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private CartRepositoryInterface $cartRepository;

    /** @var EventDispatcherInterface&\PHPUnit\Framework\MockObject\MockObject */
    private EventDispatcherInterface $eventDispatcher;
    private RemoveCartItemCommandHandler $handler;

    protected function setUp(): void
    {
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->handler = new RemoveCartItemCommandHandler(
            $this->cartRepository,
            $this->eventDispatcher
        );
    }

    public function testHandlerRemovesItemFromCart(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId, UserId::generate());

        // Add two items to the cart first
        $productId1 = ProductId::generate();
        $cart->addItem(
            $productId1,
            ProductName::fromString('Test Product 1'),
            Money::fromCents(2999, 'EUR'),
            Quantity::fromInt(2)
        );

        $productId2 = ProductId::generate();
        $cart->addItem(
            $productId2,
            ProductName::fromString('Test Product 2'),
            Money::fromCents(1999, 'EUR'),
            Quantity::fromInt(1)
        );

        $items = $cart->items();
        $cartItemToRemoveId = $items[0]->id();

        $command = new RemoveCartItemCommand(
            $cartId->value(),
            $cartItemToRemoveId->value()
        );

        $this->cartRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($cartId))
            ->willReturn($cart);

        $this->cartRepository
            ->expects($this->once())
            ->method('save')
            ->with($cart);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch');

        $this->handler->handle($command);

        // Verify one item was removed
        $this->assertCount(1, $cart->items());
    }

    public function testHandlerThrowsExceptionWhenCartNotFound(): void
    {
        $cartId = CartId::generate();
        $cartItemId = CartItemId::generate();

        $command = new RemoveCartItemCommand(
            $cartId->value(),
            $cartItemId->value()
        );

        $this->cartRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($cartId))
            ->willReturn(null);

        $this->cartRepository
            ->expects($this->never())
            ->method('save');

        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->expectException(CartNotFoundException::class);

        $this->handler->handle($command);
    }

    public function testHandlerThrowsExceptionWhenCartItemNotFound(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId, UserId::generate());
        $nonExistentItemId = CartItemId::generate();

        $command = new RemoveCartItemCommand(
            $cartId->value(),
            $nonExistentItemId->value()
        );

        $this->cartRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($cartId))
            ->willReturn($cart);

        $this->cartRepository
            ->expects($this->never())
            ->method('save');

        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->expectException(CartItemNotFoundException::class);

        $this->handler->handle($command);
    }
}
