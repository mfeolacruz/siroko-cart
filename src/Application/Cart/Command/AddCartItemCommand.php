<?php

declare(strict_types=1);

namespace App\Application\Cart\Command;

final readonly class AddCartItemCommand
{
    public function __construct(
        public string $cartId,
        public string $productId,
        public string $productName,
        public float $price,
        public string $currency,
        public int $quantity,
    ) {
    }
}
