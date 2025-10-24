<?php

declare(strict_types=1);

namespace App\Domain\Checkout\Exception;

use App\Domain\Cart\ValueObject\CartId;

final class EmptyCartException extends OrderException
{
    public static function forCart(CartId $cartId): self
    {
        return new self(
            sprintf('Cannot checkout empty cart with id <%s>', $cartId->value())
        );
    }
}
