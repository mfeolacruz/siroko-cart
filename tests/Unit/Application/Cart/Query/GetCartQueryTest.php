<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Cart\Query;

use App\Application\Cart\Query\GetCartQuery;
use PHPUnit\Framework\TestCase;

final class GetCartQueryTest extends TestCase
{
    public function testItCanBeCreated(): void
    {
        $cartId = '550e8400-e29b-41d4-a716-446655440000';

        $query = new GetCartQuery($cartId);

        $this->assertEquals($cartId, $query->cartId);
    }

    public function testItThrowsExceptionForEmptyCartId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cart ID cannot be empty');

        new GetCartQuery('');
    }
}
