<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Shared\ValueObject\Quantity;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

final class QuantityType extends IntegerType
{
    private const TYPE_NAME = 'quantity';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Quantity) {
            throw new \InvalidArgumentException('Expected Quantity instance');
        }

        return $value->value();
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Quantity
    {
        if (null === $value || $value instanceof Quantity) {
            return $value;
        }

        if (!is_int($value) && !is_string($value)) {
            throw new \InvalidArgumentException('Expected int or string value');
        }

        return Quantity::fromInt((int) $value);
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
