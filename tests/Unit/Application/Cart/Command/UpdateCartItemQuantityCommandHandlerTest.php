<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Cart\Command;

use App\Application\Cart\Command\UpdateCartItemQuantityCommand;
use App\Application\Cart\Command\UpdateCartItemQuantityCommandHandler;
use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Exception\CartItemNotFoundException;
use App\Domain\Cart\Exception\CartNotFoundException;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\CartItemId;
use App\Domain\Shared\Event\EventDispatcherInterface;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Shared\ValueObject\ProductId;
use App\Domain\Shared\ValueObject\ProductName;
use App\Domain\Shared\ValueObject\Quantity;
use App\Domain\Shared\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class UpdateCartItemQuantityCommandHandlerTest extends TestCase
{
    /** @var CartRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private CartRepositoryInterface $cartRepository;

    /** @var EventDispatcherInterface&\PHPUnit\Framework\MockObject\MockObject */
    private EventDispatcherInterface $eventDispatcher;
    private UpdateCartItemQuantityCommandHandler $handler;

    protected function setUp(): void
    {
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->handler = new UpdateCartItemQuantityCommandHandler(
            $this->cartRepository,
            $this->eventDispatcher
        );
    }

    public function testHandlerUpdatesItemQuantityInCart(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId, UserId::generate());

        // Add an item to the cart first
        $productId = ProductId::generate();
        $cart->addItem(
            $productId,
            ProductName::fromString('Test Product'),
            Money::fromCents(2999, 'EUR'),
            Quantity::fromInt(2)
        );

        $items = $cart->items();
        $cartItemId = $items[0]->id();

        $command = new UpdateCartItemQuantityCommand(
            $cartId->value(),
            $cartItemId->value(),
            5
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

        // Verify the quantity was updated
        $updatedItems = $cart->items();
        $this->assertEquals(5, $updatedItems[0]->quantity()->value());
    }

    public function testHandlerThrowsExceptionWhenCartNotFound(): void
    {
        $cartId = CartId::generate();
        $cartItemId = CartItemId::generate();

        $command = new UpdateCartItemQuantityCommand(
            $cartId->value(),
            $cartItemId->value(),
            5
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

        $command = new UpdateCartItemQuantityCommand(
            $cartId->value(),
            $nonExistentItemId->value(),
            5
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
