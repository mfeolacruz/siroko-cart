<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Cart\Command;

use App\Application\Cart\Command\UpdateCartItemQuantityCommand;
use PHPUnit\Framework\TestCase;

final class UpdateCartItemQuantityCommandTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $command = new UpdateCartItemQuantityCommand(
            '550e8400-e29b-41d4-a716-446655440000',
            '550e8400-e29b-41d4-a716-446655440001',
            5
        );

        $this->assertInstanceOf(UpdateCartItemQuantityCommand::class, $command);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $command->cartId);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440001', $command->cartItemId);
        $this->assertEquals(5, $command->quantity);
    }

    public function testValidatesPositiveQuantity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be greater than 0');

        new UpdateCartItemQuantityCommand(
            '550e8400-e29b-41d4-a716-446655440000',
            '550e8400-e29b-41d4-a716-446655440001',
            0
        );
    }

    public function testValidatesNegativeQuantity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be greater than 0');

        new UpdateCartItemQuantityCommand(
            '550e8400-e29b-41d4-a716-446655440000',
            '550e8400-e29b-41d4-a716-446655440001',
            -1
        );
    }
}
