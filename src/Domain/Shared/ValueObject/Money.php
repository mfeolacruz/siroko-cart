<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

final readonly class Money
{
    private function __construct(
        private int $amountInCents,
        private string $currency,
    ) {
        if ($amountInCents < 0) {
            throw new \InvalidArgumentException('Money amount cannot be negative');
        }
    }

    public static function fromCents(int $amountInCents, string $currency = 'EUR'): self
    {
        return new self($amountInCents, $currency);
    }

    public static function fromFloat(float $amount, string $currency = 'EUR'): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public function amountInCents(): int
    {
        return $this->amountInCents;
    }

    public function amount(): float
    {
        return $this->amountInCents / 100;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot operate with different currencies');
        }

        return new self($this->amountInCents + $other->amountInCents, $this->currency);
    }

    public function multiply(int $factor): self
    {
        return new self($this->amountInCents * $factor, $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->amountInCents === $other->amountInCents
            && $this->currency === $other->currency;
    }
}
