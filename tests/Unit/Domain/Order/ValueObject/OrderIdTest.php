<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order\ValueObject;

use App\Domain\Order\ValueObject\OrderId;
use PHPUnit\Framework\TestCase;

final class OrderIdTest extends TestCase
{
    public function testItCanBeCreatedFromString(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $orderId = OrderId::fromString($uuid);

        $this->assertEquals($uuid, $orderId->value());
    }

    public function testItCanGenerateRandomId(): void
    {
        $orderId1 = OrderId::generate();
        $orderId2 = OrderId::generate();

        $this->assertNotEquals($orderId1->value(), $orderId2->value());
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $orderId1->value()
        );
    }

    public function testItThrowsExceptionForInvalidUuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('<App\Domain\Order\ValueObject\OrderId> does not allow the value <invalid-uuid>.');

        OrderId::fromString('invalid-uuid');
    }

    public function testItThrowsExceptionForEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('<App\Domain\Order\ValueObject\OrderId> does not allow the value <>.');

        OrderId::fromString('');
    }

    public function testTwoOrderIdsWithSameValueAreEqual(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $orderId1 = OrderId::fromString($uuid);
        $orderId2 = OrderId::fromString($uuid);

        $this->assertTrue($orderId1->equals($orderId2));
    }

    public function testTwoOrderIdsWithDifferentValuesAreNotEqual(): void
    {
        $orderId1 = OrderId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $orderId2 = OrderId::fromString('550e8400-e29b-41d4-a716-446655440001');

        $this->assertFalse($orderId1->equals($orderId2));
    }

    public function testItCanBeConvertedToString(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $orderId = OrderId::fromString($uuid);

        $this->assertEquals($uuid, (string) $orderId);
    }
}
