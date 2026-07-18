<?php

namespace App\Contracts;

interface WarehouseInventoryProvider
{
    /**
     * Return stock values indexed by the external variant code.
     *
     * Example: ['GD-IP13-001' => 25, 'GD-IP14-001' => 8]
     * Codes missing from the returned array are treated as unresolved and do
     * not overwrite the last valid local stock.
     *
     * @param array<int, string> $codes
     * @return array<string, int>
     */
    public function fetchStocks(array $codes): array;
}
