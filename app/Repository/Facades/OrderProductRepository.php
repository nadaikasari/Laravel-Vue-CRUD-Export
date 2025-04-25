<?php

namespace App\Repository\Facades;
use Illuminate\Support\Facades\Facade;

class OrderProductRepository extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Repository\OrderProductRepository::class;
    }
}
