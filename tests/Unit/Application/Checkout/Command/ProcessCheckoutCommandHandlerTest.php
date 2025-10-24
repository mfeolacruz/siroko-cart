<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Checkout\Command;

use App\Application\Checkout\Command\ProcessCheckoutCommand;
use App\Application\Checkout\Command\ProcessCheckoutCommandHandler;
use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Checkout\Aggregate\Order;
use App\Domain\Checkout\Repository\OrderRepositoryInterface;
use App\Domain\Checkout\ValueObject\OrderId;
use App\Domain\Shared\Event\EventDispatcherInterface;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Shared\ValueObject\ProductId;
use App\Domain\Shared\ValueObject\ProductName;
use App\Domain\Shared\ValueObject\Quantity;
use App\Domain\Shared\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ProcessCheckoutCommandHandlerTest extends TestCase
{
    private ProcessCheckoutCommandHandler $handler;
    private CartRepositoryInterface&MockObject $cartRepository;
    private OrderRepositoryInterface&MockObject $orderRepository;
    private EventDispatcherInterface&MockObject $eventDispatcher;

    protected function setUp(): void
    {
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->handler = new ProcessCheckoutCommandHandler(
            $this->cartRepository,
            $this->orderRepository,
            $this->eventDispatcher
        );
    }

    public function testItProcessesCheckoutSuccessfully(): void
    {
        $cartId = CartId::generate();
        $userId = UserId::generate();
        $command = new ProcessCheckoutCommand($cartId->value());

        // Create a cart with items
        $cart = Cart::create($cartId, $userId);
        $cart->addItem(
            ProductId::generate(),
            ProductName::fromString('Test Product'),
            Money::fromCents(2999, 'EUR'),
            Quantity::fromInt(2)
        );

        $this->cartRepository
            ->expects($this->once())
            ->method('findById')
            ->with($cartId)
            ->willReturn($cart);

        $this->orderRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Order::class));

        $this->cartRepository
            ->expects($this->once())
            ->method('save')
            ->with($cart);

        $this->eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch');

        $orderId = $this->handler->handle($command);

        $this->assertInstanceOf(OrderId::class, $orderId);
        $this->assertTrue($cart->isExpired());
    }

    public function testItThrowsExceptionWhenCartNotFound(): void
    {
        $cartId = CartId::generate();
        $command = new ProcessCheckoutCommand($cartId->value());

        $this->cartRepository
            ->expects($this->once())
            ->method('findById')
            ->with($cartId)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cart not found');

        $this->handler->handle($command);
    }

    public function testItThrowsExceptionWhenCartIsEmpty(): void
    {
        $cartId = CartId::generate();
        $command = new ProcessCheckoutCommand($cartId->value());

        // Create empty cart
        $cart = Cart::create($cartId);

        $this->cartRepository
            ->expects($this->once())
            ->method('findById')
            ->with($cartId)
            ->willReturn($cart);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot checkout empty cart');

        $this->handler->handle($command);
    }
}
