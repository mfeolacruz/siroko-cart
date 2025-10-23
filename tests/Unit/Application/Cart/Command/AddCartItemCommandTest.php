<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Cart\Command;

use App\Application\Cart\Command\AddCartItemCommand;
use PHPUnit\Framework\TestCase;

final class AddCartItemCommandTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $command = new AddCartItemCommand(
            '550e8400-e29b-41d4-a716-446655440000',
            '550e8400-e29b-41d4-a716-446655440001',
            'Gafas Siroko Tech K3',
            49.99,
            'EUR',
            2
        );

        $this->assertInstanceOf(AddCartItemCommand::class, $command);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $command->cartId);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440001', $command->productId);
        $this->assertEquals('Gafas Siroko Tech K3', $command->productName);
        $this->assertEquals(49.99, $command->price);
        $this->assertEquals('EUR', $command->currency);
        $this->assertEquals(2, $command->quantity);
    }
}
