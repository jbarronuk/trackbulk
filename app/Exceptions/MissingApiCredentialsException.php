<?php

namespace App\Exceptions;

use RuntimeException;

class MissingApiCredentialsException extends RuntimeException
{
    public function __construct(int|string $accountId)
    {
        parent::__construct("No Royal Mail API credentials found for account [{$accountId}].");
    }
}