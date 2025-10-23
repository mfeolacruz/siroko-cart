<?php

declare(strict_types=1);

namespace App\Application\Cart\Command;

final readonly class RemoveCartItemCommand
{
    public function __construct(
        public string $cartId,
        public string $cartItemId,
    ) {
        if (empty($this->cartId)) {
            throw new \InvalidArgumentException('Cart ID cannot be empty');
        }

        if (empty($this->cartItemId)) {
            throw new \InvalidArgumentException('Cart item ID cannot be empty');
        }
    }
}
