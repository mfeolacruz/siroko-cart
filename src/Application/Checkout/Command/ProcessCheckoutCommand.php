<?php

declare(strict_types=1);

namespace App\Application\Checkout\Command;

final readonly class ProcessCheckoutCommand
{
    public function __construct(
        public string $cartId,
    ) {
    }
}
