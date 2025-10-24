<?php

declare(strict_types=1);

namespace App\Domain\Order\ValueObject;

final readonly class OrderStatus
{
    private const PENDING = 'pending';
    private const CONFIRMED = 'confirmed';
    private const SHIPPED = 'shipped';
    private const DELIVERED = 'delivered';
    private const CANCELLED = 'cancelled';

    private const VALID_STATUSES = [
        self::PENDING,
        self::CONFIRMED,
        self::SHIPPED,
        self::DELIVERED,
        self::CANCELLED,
    ];

    private function __construct(
        private string $value,
    ) {
        $this->ensureIsValidStatus($value);
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function confirmed(): self
    {
        return new self(self::CONFIRMED);
    }

    public static function shipped(): self
    {
        return new self(self::SHIPPED);
    }

    public static function delivered(): self
    {
        return new self(self::DELIVERED);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isPending(): bool
    {
        return self::PENDING === $this->value;
    }

    public function isConfirmed(): bool
    {
        return self::CONFIRMED === $this->value;
    }

    public function isShipped(): bool
    {
        return self::SHIPPED === $this->value;
    }

    public function isDelivered(): bool
    {
        return self::DELIVERED === $this->value;
    }

    public function isCancelled(): bool
    {
        return self::CANCELLED === $this->value;
    }

    public function equals(OrderStatus $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return array<string>
     */
    public static function validStatuses(): array
    {
        return self::VALID_STATUSES;
    }

    private function ensureIsValidStatus(string $value): void
    {
        if ('' === $value) {
            throw new \InvalidArgumentException('Order status cannot be empty');
        }

        if (!\in_array($value, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid order status: %s', $value));
        }
    }
}
