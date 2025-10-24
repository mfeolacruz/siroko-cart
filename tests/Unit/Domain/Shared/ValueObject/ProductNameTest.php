<?php

declare(strict_types=1);

namespace App\tests\Unit\Domain\Shared\ValueObject;

use App\Domain\Shared\ValueObject\ProductName;
use PHPUnit\Framework\TestCase;

final class ProductNameTest extends TestCase
{
    public function testCanBeCreatedWithValidValue(): void
    {
        $productName = ProductName::fromString('Gafas Siroko Tech K3');

        $this->assertInstanceOf(ProductName::class, $productName);
        $this->assertEquals('Gafas Siroko Tech K3', $productName->value());
    }

    public function testCannotBeCreatedWithEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name cannot be empty');

        ProductName::fromString('');
    }

    public function testCannotBeCreatedWithOnlyWhitespace(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name cannot be empty');

        ProductName::fromString('   ');
    }

    public function testValueIsTrimmed(): void
    {
        $productName = ProductName::fromString('  Gafas Siroko  ');

        $this->assertEquals('Gafas Siroko', $productName->value());
    }

    public function testTwoProductNamesWithSameValueAreEqual(): void
    {
        $productName1 = ProductName::fromString('Gafas Siroko');
        $productName2 = ProductName::fromString('Gafas Siroko');

        $this->assertTrue($productName1->equals($productName2));
    }

    public function testTwoProductNamesWithDifferentValueAreNotEqual(): void
    {
        $productName1 = ProductName::fromString('Gafas Siroko');
        $productName2 = ProductName::fromString('Casco Aero');

        $this->assertFalse($productName1->equals($productName2));
    }
}
