<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'opening_balance',
        'balance',
    ];

    // Auto-calculate balance if needed
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($account) {
            // On create, balance = opening_balance
            $account->balance = $account->opening_balance;
        });
    }
}
