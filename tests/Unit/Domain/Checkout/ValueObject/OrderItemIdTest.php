<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order\ValueObject;

use App\Domain\Checkout\ValueObject\OrderItemId;
use PHPUnit\Framework\TestCase;

final class OrderItemIdTest extends TestCase
{
    public function testItCanBeCreatedFromValidUuid(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $orderItemId = OrderItemId::fromString($uuid);

        $this->assertEquals($uuid, $orderItemId->value());
    }

    public function testItCanBeGenerated(): void
    {
        $orderItemId = OrderItemId::generate();

        $this->assertNotEmpty($orderItemId->value());
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $orderItemId->value()
        );
    }

    public function testItThrowsExceptionForInvalidUuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('<App\Domain\Checkout\ValueObject\OrderItemId> does not allow the value <invalid-uuid>.');

        OrderItemId::fromString('invalid-uuid');
    }

    public function testItThrowsExceptionForEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('<App\Domain\Checkout\ValueObject\OrderItemId> does not allow the value <>.');

        OrderItemId::fromString('');
    }

    public function testItCanCompareEquality(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $orderItemId1 = OrderItemId::fromString($uuid);
        $orderItemId2 = OrderItemId::fromString($uuid);
        $orderItemId3 = OrderItemId::fromString('550e8400-e29b-41d4-a716-446655440001');

        $this->assertTrue($orderItemId1->equals($orderItemId2));
        $this->assertFalse($orderItemId1->equals($orderItemId3));
    }

    public function testItCanBeConvertedToString(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $orderItemId = OrderItemId::fromString($uuid);

        $this->assertEquals($uuid, (string) $orderItemId);
    }

    public function testGeneratedIdsAreUnique(): void
    {
        $orderItemId1 = OrderItemId::generate();
        $orderItemId2 = OrderItemId::generate();

        $this->assertFalse($orderItemId1->equals($orderItemId2));
    }
}
