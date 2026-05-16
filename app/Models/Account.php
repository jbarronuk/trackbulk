<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int|null $tracking_count
 * @property-read int|null $tracking_batches_count
 * @property-read int|null $users_count
 *
 * @method static \Database\Factories\AccountFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereUpdatedAt($value)
 *
 * @mixin Model
 */
class Account extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
    ];

    /**
     * @return HasMany<User, $this>
     */
    public function users()
    {
        return $this->hasMany(User::class, 'account_id', 'id');
    }

    /**
     * @return HasMany<Tracking, $this>
     */
    public function tracking()
    {
        return $this->hasMany(Tracking::class, 'account_id', 'id');
    }

    /**
     * @return HasMany<TrackingBatch, $this>
     */
    public function trackingBatches()
    {
        return $this->hasMany(TrackingBatch::class, 'account_id', 'id');
    }
}
