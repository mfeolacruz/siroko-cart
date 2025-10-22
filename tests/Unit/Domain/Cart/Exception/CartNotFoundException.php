<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Cart\Exception;

use App\Domain\Cart\Exception\CartNotFoundException;
use App\Domain\Cart\ValueObject\CartId;
use PHPUnit\Framework\TestCase;

final class CartNotFoundExceptionTest extends TestCase
{
    public function testItCreatesExceptionWithCartId(): void
    {
        $cartId = CartId::generate();

        $exception = CartNotFoundException::withId($cartId);

        $this->assertInstanceOf(CartNotFoundException::class, $exception);
        $this->assertStringContainsString($cartId->value(), $exception->getMessage());
    }

    public function testItIsThrowable(): void
    {
        $cartId = CartId::generate();

        $this->expectException(CartNotFoundException::class);

        throw CartNotFoundException::withId($cartId);
    }
}
