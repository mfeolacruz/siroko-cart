<?php

declare(strict_types=1);

namespace App\tests\Unit\Domain\Shared\ValueObject;

use App\Domain\Shared\ValueObject\ProductId;
use App\Domain\Shared\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class ProductIdTest extends TestCase
{
    public function testCanBeGenerated(): void
    {
        $productId = ProductId::generate();

        $this->assertInstanceOf(ProductId::class, $productId);
        $this->assertInstanceOf(Uuid::class, $productId);
        $this->assertNotEmpty($productId->value());
    }

    public function testCanBeCreatedFromString(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440001';
        $productId = ProductId::fromString($uuidString);

        $this->assertInstanceOf(ProductId::class, $productId);
        $this->assertEquals($uuidString, $productId->value());
    }

    public function testTwoProductIdsWithSameValueAreEqual(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440001';
        $productId1 = ProductId::fromString($uuidString);
        $productId2 = ProductId::fromString($uuidString);

        $this->assertTrue($productId1->equals($productId2));
    }

    public function testTwoProductIdsWithDifferentValueAreNotEqual(): void
    {
        $productId1 = ProductId::generate();
        $productId2 = ProductId::generate();

        $this->assertFalse($productId1->equals($productId2));
    }
}
