<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order\Exception;

use App\Domain\Checkout\Exception\OrderException;
use PHPUnit\Framework\TestCase;

final class OrderExceptionTest extends TestCase
{
    public function testItExtendsFromDomainException(): void
    {
        $exception = new class('Test message') extends OrderException {};

        $this->assertInstanceOf(\DomainException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }
}
