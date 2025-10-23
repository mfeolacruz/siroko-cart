<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Cart\Exception;

use App\Domain\Cart\Exception\CartItemNotFoundException;
use App\Domain\Cart\ValueObject\CartItemId;
use PHPUnit\Framework\TestCase;

final class CartItemNotFoundExceptionTest extends TestCase
{
    public function testCanBeCreatedWithCartItemId(): void
    {
        $cartItemId = CartItemId::generate();

        $exception = CartItemNotFoundException::withId($cartItemId);

        $this->assertInstanceOf(CartItemNotFoundException::class, $exception);
        $this->assertStringContainsString($cartItemId->value(), $exception->getMessage());
        $this->assertStringContainsString('Cart item with id', $exception->getMessage());
        $this->assertStringContainsString('was not found', $exception->getMessage());
    }
}
