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
    protected $casts = [
        'balance' => 'decimal:2',
        'opening_balance' => 'decimal:2',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($account) {
            if ($account->balance === null) {
                $account->balance = $account->opening_balance;
            }
        });
    }

}
