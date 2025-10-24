<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Checkout\Command;

use App\Application\Checkout\Command\ProcessCheckoutCommand;
use PHPUnit\Framework\TestCase;

final class ProcessCheckoutCommandTest extends TestCase
{
    public function testItCanBeCreated(): void
    {
        $cartId = '550e8400-e29b-41d4-a716-446655440001';

        $command = new ProcessCheckoutCommand($cartId);

        $this->assertEquals($cartId, $command->cartId);
    }
}
