<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Cart\ValueObject\ProductName;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class ProductNameType extends StringType
{
    private const TYPE_NAME = 'product_name';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof ProductName) {
            throw new \InvalidArgumentException('Expected ProductName instance');
        }

        return $value->value();
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?ProductName
    {
        if (null === $value || $value instanceof ProductName) {
            return $value;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException('Expected string value');
        }

        return ProductName::fromString($value);
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
