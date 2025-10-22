<?php

declare(strict_types=1);

namespace App\Domain\Cart\Repository;

use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\ValueObject\CartId;

interface CartRepositoryInterface
{
    public function save(Cart $cart): void;

    public function findById(CartId $cartId): ?Cart;
}
