<?php

namespace App\Repository;

use App\Models\OrderProduct;

class OrderProductRepository extends IRepository
{
    public function __construct()
    {
        $this->model = new OrderProduct();
    }
}