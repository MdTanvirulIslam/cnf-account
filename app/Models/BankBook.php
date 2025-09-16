<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Account;

class BankBook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id','type','amount','note','transfer_uuid'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    protected static function booted()
    {
        // Creating: adjust account balance
        static::creating(function ($bankBook) {

            DB::transaction(function () use ($bankBook) {
                $account = $bankBook->account;

                // Check balance for withdraw / pay order / bank transfer
                if (in_array($bankBook->type, ['Withdraw','Pay Order','Bank Transfer']) && $bankBook->amount > $account->balance) {
                    throw new \Exception("Insufficient account balance!");
                }

                // Adjust balance
                if ($bankBook->type == 'Receive') {
                    $account->balance += $bankBook->amount;
                } else {
                    // for Withdraw, Pay Order, Bank Transfer => deduct
                    $account->balance -= $bankBook->amount;
                }

                $account->save();

                Log::info('BankBook created', [
                    'bankbook_temp_id' => $bankBook->id,
                    'account_id'       => $account->id,
                    'type'             => $bankBook->type,
                    'amount'           => $bankBook->amount
                ]);
            });
        });

        // Updating: reverse old record effect then apply new
        static::updating(function ($bankBook) {

            DB::transaction(function () use ($bankBook) {

                $original = $bankBook->getOriginal();

                $oldAccount = Account::find($original['account_id']);
                if ($oldAccount) {
                    if ($original['type'] == 'Receive') {
                        $oldAccount->balance -= $original['amount'];
                    } else {
                        $oldAccount->balance += $original['amount'];
                    }
                    $oldAccount->save();
                }

                $newAccount = $bankBook->account;

                // Check balance for withdraw / pay order / bank transfer
                if ($newAccount && in_array($bankBook->type, ['Withdraw','Pay Order','Bank Transfer']) && $bankBook->amount > $newAccount->balance) {
                    throw new \Exception("Insufficient account balance!");
                }

                if ($newAccount) {
                    if ($bankBook->type == 'Receive') {
                        $newAccount->balance += $bankBook->amount;
                    } else {
                        $newAccount->balance -= $bankBook->amount;
                    }
                    $newAccount->save();
                }

                Log::info('BankBook updated', [
                    'bankbook_id'     => $bankBook->id,
                    'old_account_id'  => $original['account_id'],
                    'new_account_id'  => $bankBook->account_id,
                    'type'            => $bankBook->type,
                    'amount'          => $bankBook->amount
                ]);

            });
        });

        // Deleting: reverse the effect on account (covers soft-delete too)
        static::deleting(function ($bankBook) {
            DB::transaction(function () use ($bankBook) {
                $account = Account::find($bankBook->account_id);
                if (!$account) {
                    return;
                }

                if ($bankBook->type == 'Receive') {
                    // remove previously added receive amount
                    $account->balance -= $bankBook->amount;
                } else {
                    // restore previously deducted amount
                    $account->balance += $bankBook->amount;
                }

                $account->save();

                Log::info('BankBook deleted (balance reversed)', [
                    'bankbook_id' => $bankBook->id,
                    'account_id'  => $bankBook->account_id,
                    'type'        => $bankBook->type,
                    'amount'      => $bankBook->amount
                ]);
            });
        });
    }
}
