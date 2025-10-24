<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Cart\Query;

use App\Application\Cart\Query\GetCartQuery;
use App\Application\Cart\Query\GetCartQueryHandler;
use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Exception\CartNotFoundException;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Cart\ValueObject\Money;
use App\Domain\Cart\ValueObject\ProductId;
use App\Domain\Cart\ValueObject\ProductName;
use App\Domain\Cart\ValueObject\Quantity;
use App\Domain\Cart\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class GetCartQueryHandlerTest extends TestCase
{
    /** @var CartRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private CartRepositoryInterface $cartRepository;
    private GetCartQueryHandler $handler;

    protected function setUp(): void
    {
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->handler = new GetCartQueryHandler($this->cartRepository);
    }

    public function testHandlerReturnsCartWithItems(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId, UserId::generate());

        // Add items to cart
        $cart->addItem(
            ProductId::generate(),
            ProductName::fromString('Product 1'),
            Money::fromCents(2999, 'EUR'),
            Quantity::fromInt(2)
        );

        $cart->addItem(
            ProductId::generate(),
            ProductName::fromString('Product 2'),
            Money::fromCents(1999, 'EUR'),
            Quantity::fromInt(1)
        );

        $query = new GetCartQuery($cartId->value());

        $this->cartRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($cartId))
            ->willReturn($cart);

        $result = $this->handler->handle($query);

        $this->assertSame($cart, $result);
        $this->assertCount(2, $result->items());
        $this->assertEquals(7997, $result->total()->amountInCents());
    }

    public function testHandlerReturnsEmptyCart(): void
    {
        $cartId = CartId::generate();
        $cart = Cart::create($cartId);

        $query = new GetCartQuery($cartId->value());

        $this->cartRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($cartId))
            ->willReturn($cart);

        $result = $this->handler->handle($query);

        $this->assertSame($cart, $result);
        $this->assertTrue($result->isEmpty());
        $this->assertEquals(0, $result->total()->amountInCents());
    }

    public function testHandlerThrowsExceptionWhenCartNotFound(): void
    {
        $cartId = CartId::generate();
        $query = new GetCartQuery($cartId->value());

        $this->cartRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($cartId))
            ->willReturn(null);

        $this->expectException(CartNotFoundException::class);

        $this->handler->handle($query);
    }
}
