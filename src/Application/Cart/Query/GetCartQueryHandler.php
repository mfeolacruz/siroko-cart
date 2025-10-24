<?php

declare(strict_types=1);

namespace App\Application\Cart\Query;

use App\Domain\Cart\Aggregate\Cart;
use App\Domain\Cart\Exception\CartNotFoundException;
use App\Domain\Cart\Repository\CartRepositoryInterface;
use App\Domain\Cart\ValueObject\CartId;

final readonly class GetCartQueryHandler
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
    ) {
    }

    public function handle(GetCartQuery $query): Cart
    {
        $cartId = CartId::fromString($query->cartId);

        $cart = $this->cartRepository->findById($cartId);

        if (null === $cart) {
            throw CartNotFoundException::withId($cartId);
        }

        return $cart;
    }
}
