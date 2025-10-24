<?php

declare(strict_types=1);

namespace App\Domain\Checkout\Exception;

use App\Domain\Checkout\ValueObject\OrderId;

final class OrderNotFoundException extends OrderException
{
    public static function withId(OrderId $orderId): self
    {
        return new self(
            sprintf('Order with id <%s> not found', $orderId->value())
        );
    }
}
