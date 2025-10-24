<?php

declare(strict_types=1);

namespace App\Domain\Checkout\Repository;

use App\Domain\Checkout\Aggregate\Order;
use App\Domain\Checkout\ValueObject\OrderId;

interface OrderRepositoryInterface
{
    public function save(Order $order): void;

    public function findById(OrderId $orderId): ?Order;
}
