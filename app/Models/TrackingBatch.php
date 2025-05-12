<?php

namespace App\Models;

use Illuminate\Bus\Batchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * @var array
     */
    protected $fillable = [
        'account_id'
    ];

    /**
     * Get the account associated with this tracking.
     */
    public function tracking()
    {
        return $this->hasMany(Tracking::class);
    }
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }
}
