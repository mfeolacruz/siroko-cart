<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Cart\Command;

use App\Application\Cart\Command\RemoveCartItemCommand;
use PHPUnit\Framework\TestCase;

final class RemoveCartItemCommandTest extends TestCase
{
    public function testItCanBeCreated(): void
    {
        $cartId = '550e8400-e29b-41d4-a716-446655440000';
        $cartItemId = '550e8400-e29b-41d4-a716-446655440001';

        $command = new RemoveCartItemCommand($cartId, $cartItemId);

        $this->assertEquals($cartId, $command->cartId);
        $this->assertEquals($cartItemId, $command->cartItemId);
    }

    public function testItThrowsExceptionForInvalidCartId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cart ID cannot be empty');

        new RemoveCartItemCommand('', '550e8400-e29b-41d4-a716-446655440001');
    }

    public function testItThrowsExceptionForInvalidCartItemId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cart item ID cannot be empty');

        new RemoveCartItemCommand('550e8400-e29b-41d4-a716-446655440000', '');
    }
}
