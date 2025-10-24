<?php

declare(strict_types=1);

namespace App\Domain\Order\Repository;

use App\Domain\Order\Aggregate\Order;
use App\Domain\Order\ValueObject\OrderId;

interface OrderRepositoryInterface
{
    public function save(Order $order): void;

    public function findById(OrderId $orderId): ?Order;
}
