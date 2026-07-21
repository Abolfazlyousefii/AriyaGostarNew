<?php

namespace App\Contracts;

interface WarehouseInventoryProvider
{
    /**
     * Return stock quantities keyed by the website stock code.
     *
     * Example:
     * [
     *     'ARY-V-0000000001' => 25,
     *     'ARY-V-0000000002' => 0,
     * ]
     */
    public function fetchStocks(array $stockCodes): array;
}
