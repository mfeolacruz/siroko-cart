<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Cart\ValueObject\CartItemId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class CartItemIdType extends StringType
{
    private const TYPE_NAME = 'cart_item_id';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof CartItemId) {
            throw new \InvalidArgumentException('Expected CartItemId instance');
        }

        return $value->value();
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?CartItemId
    {
        if (null === $value || $value instanceof CartItemId) {
            return $value;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException('Expected string value');
        }

        return CartItemId::fromString($value);
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
