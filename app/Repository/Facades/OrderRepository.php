<?php

namespace App\Repository\Facades;
use Illuminate\Support\Facades\Facade;

class OrderRepository extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Repository\OrderRepository::class;
    }
}
