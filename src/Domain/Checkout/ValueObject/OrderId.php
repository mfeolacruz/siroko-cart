<?php

declare(strict_types=1);

namespace App\Domain\Checkout\ValueObject;

use App\Domain\Shared\ValueObject\Uuid;

final readonly class OrderId extends Uuid
{
}
