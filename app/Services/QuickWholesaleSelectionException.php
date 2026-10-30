<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class QuickWholesaleSelectionException extends RuntimeException
{
    public function __construct(string $message, private readonly ?int $priceId = null)
    {
        parent::__construct($message);
    }

    public function priceId(): ?int
    {
        return $this->priceId;
    }
}
