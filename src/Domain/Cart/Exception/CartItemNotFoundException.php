<?php

declare(strict_types=1);

namespace App\Domain\Cart\Exception;

use App\Domain\Cart\ValueObject\CartItemId;

final class CartItemNotFoundException extends CartException
{
    public static function withId(CartItemId $cartItemId): self
    {
        return new self(sprintf('Cart item with id "%s" was not found', $cartItemId->value()));
    }
}
