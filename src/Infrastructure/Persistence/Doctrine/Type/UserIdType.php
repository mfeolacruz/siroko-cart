<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Shared\ValueObject\UserId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class UserIdType extends StringType
{
    private const NAME = 'user_id';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof UserId) {
            return $value->value();
        }

        throw new \InvalidArgumentException('Invalid UserId value');
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?UserId
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException('UserId value must be a string');
        }

        return UserId::fromString($value);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
