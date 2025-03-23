<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * @var array
     */
    protected $fillable = [
        'type',
    ];

    /**
     * Get the users associated with this account.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'account_id', 'id');
    }
        /**
     * Get the users associated with this account.
     */
    public function tracking()
    {
        return $this->hasMany(Tracking::class, 'account_id', 'id');
    }
}
