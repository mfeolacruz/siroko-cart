<?php

declare(strict_types=1);

namespace App\Domain\Cart\Exception;

use App\Domain\Cart\ValueObject\CartId;

final class CartNotFoundException extends CartException
{
    public static function withId(CartId $cartId): self
    {
        return new self(
            sprintf('Cart with id <%s> not found', $cartId->value())
        );
    }
}
