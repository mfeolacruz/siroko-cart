<?php

declare(strict_types=1);

namespace App\Application\Cart\Command;

final readonly class CreateCartCommand
{
    public function __construct(
        public ?string $userId,
    ) {
    }
}
