<?php

namespace App\Models;

use Illuminate\Bus\Batchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * @var array
     */
    protected $fillable = [
        'number',
        'tracking_id',
        'response',
    ];

    /**
     * Get the account associated with this tracking.
     */
    public function tracking()
    {
        return $this->belongsTo(Tracking::class, 'tracking_id', 'id');
    }
}
