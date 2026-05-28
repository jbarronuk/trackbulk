<?php

namespace App\Jobs;

use App\Enums\TrackingStatus;
use App\Models\Tracking;
use App\Models\TrackingHistory;
use App\Services\RoyalMail\RoyalMailClient;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Queries the Royal Mail tracking API for the latest status of a mail piece
 * and updates the associated Tracking record.
 */
class QueryRoyalMailTracking implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const STATUS_SUCCESS = '200';

    public function __construct(
        private readonly Tracking $tracking,
    ) {
        $this->tracking->status = TrackingStatus::Queued->value;
        $this->tracking->save();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $mailPieceId = $this->tracking->number;

        $client = new RoyalMailClient(
            $this->tracking->account->royalMailCredentials(),
        );

        $response = $client->trackingSummary($mailPieceId);

        TrackingHistory::create([
            'number' => $mailPieceId,
            'response' => $response,
            'tracking_id' => $this->tracking->id,
        ]);

        if (! $response->successful()) {
            Log::error('Royal Mail API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return;
        }

        $mailPiece = $response->json('mailPieces.0');

        if (! $this->isValidMailPiece($mailPiece)) {
            return;
        }

        $this->updateTracking($mailPiece);
    }

    /**
     * Determine whether the API returned a usable mail piece for this tracking record.
     */
    private function isValidMailPiece(?array $mailPiece): bool
    {
        return $mailPiece !== null
            && ($mailPiece['mailPieceId'] ?? null) == $this->tracking->number
            && ($mailPiece['status'] ?? null) === self::STATUS_SUCCESS
            && isset($mailPiece['summary']['statusCategory']);
    }

    /**
     * Persist the latest status and summary details to the tracking record.
     */
    private function updateTracking(array $mailPiece): void
    {
        $summary = $mailPiece['summary'];

        $this->tracking->status = $this->mapStatus($summary['statusCategory']);

        if (isset($summary['summaryLine'])) {
            $this->tracking->summary_response = $summary['summaryLine'];

            if ($this->tracking->status === TrackingStatus::Unknown->value) {
                Log::info($summary['summaryLine']);
            }
        }

        $this->tracking->response = $mailPiece;
        $this->tracking->save();
    }

    /**
     * Map a Royal Mail status category to an internal tracking status.
     */
    private function mapStatus(string $statusCategory): string
    {
        return match ($statusCategory) {
            'Ready for Delivery' => TrackingStatus::RoyalMailReadyForDelivery->value,
            'Delivered' => TrackingStatus::RoyalMailDelivered->value,
            default => TrackingStatus::Unknown->value,
        };
    }
}