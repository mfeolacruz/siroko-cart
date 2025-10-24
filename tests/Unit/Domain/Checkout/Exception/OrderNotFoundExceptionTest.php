<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order\Exception;

use App\Domain\Checkout\Exception\OrderException;
use App\Domain\Checkout\Exception\OrderNotFoundException;
use App\Domain\Checkout\ValueObject\OrderId;
use PHPUnit\Framework\TestCase;

final class OrderNotFoundExceptionTest extends TestCase
{
    public function testItExtendsFromOrderException(): void
    {
        $orderId = OrderId::generate();
        $exception = OrderNotFoundException::withId($orderId);

        $this->assertInstanceOf(OrderException::class, $exception);
        $this->assertInstanceOf(\DomainException::class, $exception);
    }

    public function testItCanBeCreatedWithOrderId(): void
    {
        $orderId = OrderId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $exception = OrderNotFoundException::withId($orderId);

        $expectedMessage = 'Order with id <550e8400-e29b-41d4-a716-446655440000> not found';
        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    public function testItGeneratesCorrectMessageForDifferentOrderIds(): void
    {
        $orderId1 = OrderId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $orderId2 = OrderId::fromString('550e8400-e29b-41d4-a716-446655440002');

        $exception1 = OrderNotFoundException::withId($orderId1);
        $exception2 = OrderNotFoundException::withId($orderId2);

        $this->assertStringContainsString('550e8400-e29b-41d4-a716-446655440001', $exception1->getMessage());
        $this->assertStringContainsString('550e8400-e29b-41d4-a716-446655440002', $exception2->getMessage());
        $this->assertNotEquals($exception1->getMessage(), $exception2->getMessage());
    }
}
