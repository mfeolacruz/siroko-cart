<?php

declare(strict_types=1);

namespace App\Domain\Cart\ValueObject;

final readonly class Quantity
{
    private function __construct(
        private int $value,
    ) {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero');
        }
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function increase(self $other): self
    {
        return new self($this->value + $other->value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
