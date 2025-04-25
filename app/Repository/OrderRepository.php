<?php

namespace App\Repository;

use App\Models\Order;

class OrderRepository extends IRepository
{
    public function __construct()
    {
        $this->model = new Order();
    }
}