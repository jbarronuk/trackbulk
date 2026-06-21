<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property int $id
 * @property int $account_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $formatted_created_at
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
    ];

    protected $appends = ['formatted_created_at'];

    /**
     * @return HasMany<Tracking, $this>
     */
    public function tracking(): HasMany
    {
        return $this->hasMany(Tracking::class);
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    protected function formattedCreatedAt(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->created_at?->format('H:i'),
        );
    }
}
