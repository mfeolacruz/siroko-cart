<?php

declare(strict_types=1);

namespace App\Application\Cart\Command;

final readonly class UpdateCartItemQuantityCommand
{
    public function __construct(
        public string $cartId,
        public string $cartItemId,
        public int $quantity,
    ) {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than 0');
        }
    }
}
