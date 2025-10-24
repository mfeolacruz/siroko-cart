<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Cart\ValueObject;

use App\Domain\Cart\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testCanBeCreatedFromCents(): void
    {
        $money = Money::fromCents(4999, 'EUR');

        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals(4999, $money->amountInCents());
        $this->assertEquals(49.99, $money->amount());
        $this->assertEquals('EUR', $money->currency());
    }

    public function testCanBeCreatedFromFloat(): void
    {
        $money = Money::fromFloat(49.99, 'EUR');

        $this->assertInstanceOf(Money::class, $money);
        $this->assertEquals(4999, $money->amountInCents());
        $this->assertEquals(49.99, $money->amount());
    }

    public function testCannotBeCreatedWithNegativeAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Money amount cannot be negative');

        Money::fromCents(-100, 'EUR');
    }

    public function testCanBeAdded(): void
    {
        $money1 = Money::fromCents(1000, 'EUR');
        $money2 = Money::fromCents(500, 'EUR');

        $result = $money1->add($money2);

        $this->assertEquals(1500, $result->amountInCents());
        $this->assertEquals('EUR', $result->currency());
    }

    public function testCannotAddDifferentCurrencies(): void
    {
        $money1 = Money::fromCents(1000, 'EUR');
        $money2 = Money::fromCents(500, 'USD');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot operate with different currencies');

        $money1->add($money2);
    }

    public function testCanBeMultiplied(): void
    {
        $money = Money::fromCents(1000, 'EUR');

        $result = $money->multiply(3);

        $this->assertEquals(3000, $result->amountInCents());
        $this->assertEquals('EUR', $result->currency());
    }

    public function testTwoMoneyWithSameValueAreEqual(): void
    {
        $money1 = Money::fromCents(1000, 'EUR');
        $money2 = Money::fromCents(1000, 'EUR');

        $this->assertTrue($money1->equals($money2));
    }

    public function testTwoMoneyWithDifferentAmountAreNotEqual(): void
    {
        $money1 = Money::fromCents(1000, 'EUR');
        $money2 = Money::fromCents(2000, 'EUR');

        $this->assertFalse($money1->equals($money2));
    }

    public function testTwoMoneyWithDifferentCurrencyAreNotEqual(): void
    {
        $money1 = Money::fromCents(1000, 'EUR');
        $money2 = Money::fromCents(1000, 'USD');

        $this->assertFalse($money1->equals($money2));
    }

    public function testDefaultCurrencyIsEUR(): void
    {
        $money = Money::fromCents(1000);

        $this->assertEquals('EUR', $money->currency());
    }
}
