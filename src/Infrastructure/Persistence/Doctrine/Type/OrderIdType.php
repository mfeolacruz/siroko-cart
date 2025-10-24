<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Checkout\ValueObject\OrderId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

final class OrderIdType extends Type
{
    public const NAME = 'order_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?OrderId
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if ($value instanceof OrderId) {
            return $value;
        }

        if (!is_string($value)) {
            throw ConversionException::conversionFailed($value, self::NAME);
        }

        try {
            return OrderId::fromString($value);
        } catch (\InvalidArgumentException $e) {
            throw ConversionException::conversionFailed($value, self::NAME);
        }
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof OrderId) {
            return $value->value();
        }

        throw ConversionException::conversionFailedInvalidType($value, self::NAME, ['null', OrderId::class]);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
