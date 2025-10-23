<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Cart\ValueObject\Money;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

final class MoneyType extends JsonType
{
    private const TYPE_NAME = 'money';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Money) {
            throw new \InvalidArgumentException('Expected Money instance');
        }

        $encoded = json_encode([
            'amount_in_cents' => $value->amountInCents(),
            'currency' => $value->currency(),
        ]);

        if (false === $encoded) {
            throw new \RuntimeException('Failed to encode Money to JSON');
        }

        return $encoded;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Money
    {
        if (null === $value || $value instanceof Money) {
            return $value;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException('Expected string value');
        }

        $data = json_decode($value, true);

        if (!is_array($data)) {
            throw new \RuntimeException('Failed to decode Money from JSON');
        }

        if (!isset($data['amount_in_cents']) || !isset($data['currency'])) {
            throw new \RuntimeException('Invalid Money JSON structure');
        }

        if (!is_int($data['amount_in_cents']) || !is_string($data['currency'])) {
            throw new \RuntimeException('Invalid Money data types');
        }

        return Money::fromCents($data['amount_in_cents'], $data['currency']);
    }

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
