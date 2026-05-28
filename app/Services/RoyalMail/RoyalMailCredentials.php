<?php

namespace App\Services\RoyalMail;

final readonly class RoyalMailCredentials
{
    public function __construct(
        public string $clientId,
        public string $clientSecret,
    ) {}
}