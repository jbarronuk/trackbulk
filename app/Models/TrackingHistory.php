<?php

namespace App\Models;

use Illuminate\Bus\Batchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $number
 * @property string $response
 * @property int $tracking_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingHistory whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingHistory whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingHistory whereTrackingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingHistory whereUpdatedAt($value)
 * @mixin \Illuminate\Database\Eloquent\Model
 */
class TrackingHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tracking_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'number',
        'tracking_id',
        'response',
    ];

    /**
    * @return BelongsTo<Tracking, $this>
    */
    public function tracking()
    {
        return $this->belongsTo(Tracking::class, 'tracking_id', 'id');
    }
}
