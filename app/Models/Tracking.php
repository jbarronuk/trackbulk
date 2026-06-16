<?php

namespace App\Models;

use App\Enums\TrackingStatus;
use App\Enums\TrackingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $number
 * @property int|TrackingType $type
 * @property int|TrackingStatus|null $status
 * @property string|null $response
 * @property string|null $summary_response
 * @property int $account_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $tracking_batch_id
 *
 * @method static \Database\Factories\TrackingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereSummaryResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereTrackingBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereUpdatedAt($value)
 *
 * @mixin Model
 */
class Tracking extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tracking';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'number',
        'type',
        'status',
        'response',
        'summary_response'
    ];

    protected $casts = [
        'status'    => TrackingStatus::class,
        'type'      => TrackingType::class
    ];

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    /**
     * @return BelongsTo<TrackingBatch, $this>
     */
    public function trackingBatch(): BelongsTo
    {
        return $this->belongsTo(TrackingBatch::class, 'tracking_batch_id', 'id');
    }
}
