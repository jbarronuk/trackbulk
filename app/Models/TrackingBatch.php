<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $account_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $formatted_created_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingBatch whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackingBatch whereUpdatedAt($value)
 *
 * @mixin Model
 */
class TrackingBatch extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tracking_batch';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'account_id',
    ];

    /**
     * @return HasMany<Tracking, $this>
     */
    public function tracking()
    {
        return $this->hasMany(Tracking::class);
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }
}
