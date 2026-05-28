<?php

namespace App\Services\RoyalMail;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class RoyalMailClient
{
    private const API_ENDPOINT = 'https://api.royalmail.net/mailpieces/v2/summary';

    public function __construct(
        private readonly RoyalMailCredentials $credentials,
    ) {}

    /**
     * Fetch the tracking summary for a given mail piece.
     */
    public function trackingSummary(string $mailPieceId): Response
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
            'X-IBM-Client-Id' => $this->credentials->clientId,
            'X-IBM-Client-Secret' => $this->credentials->clientSecret,
        ])->get(self::API_ENDPOINT, [
            'mailPieceId' => $mailPieceId,
        ]);
    }
}