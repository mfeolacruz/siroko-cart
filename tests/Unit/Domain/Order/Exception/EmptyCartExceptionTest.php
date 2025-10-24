<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order\Exception;

use App\Domain\Cart\ValueObject\CartId;
use App\Domain\Order\Exception\EmptyCartException;
use App\Domain\Order\Exception\OrderException;
use PHPUnit\Framework\TestCase;

final class EmptyCartExceptionTest extends TestCase
{
    public function testItExtendsFromOrderException(): void
    {
        $cartId = CartId::generate();
        $exception = EmptyCartException::forCart($cartId);

        $this->assertInstanceOf(OrderException::class, $exception);
        $this->assertInstanceOf(\DomainException::class, $exception);
    }

    public function testItCanBeCreatedWithCartId(): void
    {
        $cartId = CartId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $exception = EmptyCartException::forCart($cartId);

        $expectedMessage = 'Cannot checkout empty cart with id <550e8400-e29b-41d4-a716-446655440000>';
        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    public function testItGeneratesCorrectMessageForDifferentCartIds(): void
    {
        $cartId1 = CartId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $cartId2 = CartId::fromString('550e8400-e29b-41d4-a716-446655440002');

        $exception1 = EmptyCartException::forCart($cartId1);
        $exception2 = EmptyCartException::forCart($cartId2);

        $this->assertStringContainsString('550e8400-e29b-41d4-a716-446655440001', $exception1->getMessage());
        $this->assertStringContainsString('550e8400-e29b-41d4-a716-446655440002', $exception2->getMessage());
        $this->assertNotEquals($exception1->getMessage(), $exception2->getMessage());
    }
}
