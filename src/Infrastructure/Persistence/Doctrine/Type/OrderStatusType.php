<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Checkout\ValueObject\OrderStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

final class OrderStatusType extends Type
{
    public const NAME = 'order_status';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?OrderStatus
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if ($value instanceof OrderStatus) {
            return $value;
        }

        if (!is_string($value)) {
            throw ConversionException::conversionFailed($value, self::NAME);
        }

        try {
            return OrderStatus::fromString($value);
        } catch (\InvalidArgumentException $e) {
            throw ConversionException::conversionFailed($value, self::NAME);
        }
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof OrderStatus) {
            return $value->value();
        }

        throw ConversionException::conversionFailedInvalidType($value, self::NAME, ['null', OrderStatus::class]);
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
