<?php

declare(strict_types=1);

namespace App\Domain\Cart\ValueObject;

final readonly class ProductName
{
    private function __construct(
        private string $value,
    ) {
        $trimmed = trim($value);

        if ('' === $trimmed) {
            throw new \InvalidArgumentException('Product name cannot be empty');
        }
    }

    public static function fromString(string $value): self
    {
        return new self(trim($value));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
