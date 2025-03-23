<?php

namespace App\Jobs;

use App\Enums\TrackingStatus;
use App\Models\Tracking;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Query implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;
    
    protected Tracking $track;
    /**
     * Create a new job instance.
     */
    public function __construct(Tracking $track)
    {
        $this->track = $track;
        $this->track->status = TrackingStatus::Queued->value;
        $this->track->save();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $mailPieceId = $this->track->number;

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'X-IBM-Client-Id' => $this->track->account->users[0]->client_id,
            'X-IBM-Client-Secret' => $this->track->account->users[0]->client_secret,
        ])->get("https://api.royalmail.net/mailpieces/v2/summary", [
            'mailPieceId' => $mailPieceId
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['mailPieces'][0]) &&
                isset($data['mailPieces'][0]['mailPieceId']) &&
                $data['mailPieces'][0]['mailPieceId'] == $this->track->number &&
                $data['mailPieces'][0]['status'] == '200' &&
                isset($data['mailPieces'][0]['summary']) &&
                isset($data['mailPieces'][0]['summary']['statusCategory'])
            ){
                $this->track->status = $this->status($data['mailPieces'][0]['summary']['statusCategory']);
                if (isset($data['mailPieces'][0]['summary']['summaryLine'])) {
                    $this->track->summary_response = $data['mailPieces'][0]['summary']['summaryLine'];
                    if ($this->track->status === TrackingStatus::Unknown->value) {
                        Log::info($data['mailPieces'][0]['summary']['summaryLine']);
                    }
                }
                $this->track->response = $data['mailPieces'][0];
                $this->track->save();
            }
        } else {
            // Handle error
            Log::error("Royal Mail API request failed", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        }
    }
    protected function status($statusCategory)
    {
        switch ($statusCategory) {
            case 'Ready for Delivery':
                return TrackingStatus::RoyalMailReadyForDelivery->value;
            case 'Delivered':
                return TrackingStatus::RoyalMailDelivered->value;
            default:
                return TrackingStatus::Unknown->value;
        }
    }
}
