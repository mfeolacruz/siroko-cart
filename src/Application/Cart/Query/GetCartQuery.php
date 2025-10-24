<?php

declare(strict_types=1);

namespace App\Application\Cart\Query;

final readonly class GetCartQuery
{
    public function __construct(
        public string $cartId,
    ) {
        if (empty($this->cartId)) {
            throw new \InvalidArgumentException('Cart ID cannot be empty');
        }
    }
}
