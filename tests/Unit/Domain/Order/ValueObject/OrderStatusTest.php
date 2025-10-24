<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order\ValueObject;

use App\Domain\Order\ValueObject\OrderStatus;
use PHPUnit\Framework\TestCase;

final class OrderStatusTest extends TestCase
{
    public function testItCanBeCreatedAsPending(): void
    {
        $status = OrderStatus::pending();

        $this->assertEquals('pending', $status->value());
        $this->assertTrue($status->isPending());
        $this->assertFalse($status->isConfirmed());
        $this->assertFalse($status->isShipped());
        $this->assertFalse($status->isDelivered());
        $this->assertFalse($status->isCancelled());
    }

    public function testItCanBeCreatedAsConfirmed(): void
    {
        $status = OrderStatus::confirmed();

        $this->assertEquals('confirmed', $status->value());
        $this->assertFalse($status->isPending());
        $this->assertTrue($status->isConfirmed());
        $this->assertFalse($status->isShipped());
        $this->assertFalse($status->isDelivered());
        $this->assertFalse($status->isCancelled());
    }

    public function testItCanBeCreatedAsShipped(): void
    {
        $status = OrderStatus::shipped();

        $this->assertEquals('shipped', $status->value());
        $this->assertFalse($status->isPending());
        $this->assertFalse($status->isConfirmed());
        $this->assertTrue($status->isShipped());
        $this->assertFalse($status->isDelivered());
        $this->assertFalse($status->isCancelled());
    }

    public function testItCanBeCreatedAsDelivered(): void
    {
        $status = OrderStatus::delivered();

        $this->assertEquals('delivered', $status->value());
        $this->assertFalse($status->isPending());
        $this->assertFalse($status->isConfirmed());
        $this->assertFalse($status->isShipped());
        $this->assertTrue($status->isDelivered());
        $this->assertFalse($status->isCancelled());
    }

    public function testItCanBeCreatedAsCancelled(): void
    {
        $status = OrderStatus::cancelled();

        $this->assertEquals('cancelled', $status->value());
        $this->assertFalse($status->isPending());
        $this->assertFalse($status->isConfirmed());
        $this->assertFalse($status->isShipped());
        $this->assertFalse($status->isDelivered());
        $this->assertTrue($status->isCancelled());
    }

    public function testItCanBeCreatedFromString(): void
    {
        $status = OrderStatus::fromString('confirmed');

        $this->assertEquals('confirmed', $status->value());
        $this->assertTrue($status->isConfirmed());
    }

    public function testItThrowsExceptionForInvalidStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid order status: invalid_status');

        OrderStatus::fromString('invalid_status');
    }

    public function testItThrowsExceptionForEmptyStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Order status cannot be empty');

        OrderStatus::fromString('');
    }

    public function testTwoStatusesWithSameValueAreEqual(): void
    {
        $status1 = OrderStatus::pending();
        $status2 = OrderStatus::fromString('pending');

        $this->assertTrue($status1->equals($status2));
    }

    public function testTwoStatusesWithDifferentValuesAreNotEqual(): void
    {
        $status1 = OrderStatus::pending();
        $status2 = OrderStatus::confirmed();

        $this->assertFalse($status1->equals($status2));
    }

    public function testItCanBeConvertedToString(): void
    {
        $status = OrderStatus::pending();

        $this->assertEquals('pending', (string) $status);
    }

    public function testItProvidesAllValidStatuses(): void
    {
        $validStatuses = OrderStatus::validStatuses();

        $expected = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        $this->assertEquals($expected, $validStatuses);
    }
}
