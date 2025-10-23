<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Cart\ValueObject\ProductId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class ProductIdType extends StringType
{
    private const TYPE_NAME = 'product_id';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof ProductId) {
            throw new \InvalidArgumentException('Expected ProductId instance');
        }

        return $value->value();
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?ProductId
    {
        if (null === $value || $value instanceof ProductId) {
            return $value;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException('Expected string value');
        }

        return ProductId::fromString($value);
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
