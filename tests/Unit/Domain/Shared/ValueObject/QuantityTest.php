<?php

declare(strict_types=1);

namespace App\tests\Unit\Domain\Shared\ValueObject;

use App\Domain\Shared\ValueObject\Quantity;
use PHPUnit\Framework\TestCase;

final class QuantityTest extends TestCase
{
    public function testCanBeCreatedWithValidValue(): void
    {
        $quantity = Quantity::fromInt(5);

        $this->assertInstanceOf(Quantity::class, $quantity);
        $this->assertEquals(5, $quantity->value());
    }

    public function testCannotBeCreatedWithZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be greater than zero');

        Quantity::fromInt(0);
    }

    public function testCannotBeCreatedWithNegativeValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be greater than zero');

        Quantity::fromInt(-5);
    }

    public function testCanBeIncreased(): void
    {
        $quantity = Quantity::fromInt(3);
        $increased = $quantity->increase(Quantity::fromInt(2));

        $this->assertEquals(5, $increased->value());
    }

    public function testTwoQuantitiesWithSameValueAreEqual(): void
    {
        $quantity1 = Quantity::fromInt(10);
        $quantity2 = Quantity::fromInt(10);

        $this->assertTrue($quantity1->equals($quantity2));
    }

    public function testTwoQuantitiesWithDifferentValueAreNotEqual(): void
    {
        $quantity1 = Quantity::fromInt(5);
        $quantity2 = Quantity::fromInt(10);

        $this->assertFalse($quantity1->equals($quantity2));
    }
}
