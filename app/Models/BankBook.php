<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class BankBook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id', 'type', 'amount', 'note', 'transfer_uuid', 'adjust_balance'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    protected static function booted()
    {
        // Creating
        static::creating(function ($bankBook) {
            $account = $bankBook->account;
            if (!$account) throw new \Exception("Account not found for BankBook entry!");

            // Only adjust balance if adjust_balance is true
            if (!isset($bankBook->adjust_balance) || $bankBook->adjust_balance) {
                if (in_array($bankBook->type, ['Withdraw', 'Pay Order', 'Bank Transfer']) && $bankBook->amount > $account->balance) {
                    throw new \Exception("Insufficient account balance!");
                }

                if ($bankBook->type == 'Receive') {
                    $account->balance += $bankBook->amount;
                } else {
                    $account->balance -= $bankBook->amount;
                }

                $account->save();
            }

            Log::info('BankBook created', [
                'bankbook_id' => $bankBook->id,
                'account_id'  => $account->id,
                'type'        => $bankBook->type,
                'amount'      => $bankBook->amount,
                'adjusted'    => $bankBook->adjust_balance ?? true,
                'new_balance' => $account->balance
            ]);
        });

        // Updating
        static::updating(function ($bankBook) {
            $original = $bankBook->getOriginal();

            $oldAccount = Account::find($original['account_id']);
            if ($oldAccount && (!isset($bankBook->adjust_balance) || $bankBook->adjust_balance)) {
                if ($original['type'] == 'Receive') {
                    $oldAccount->balance -= $original['amount'];
                } else {
                    $oldAccount->balance += $original['amount'];
                }
                $oldAccount->save();
            }

            $newAccount = $bankBook->account;
            if ($newAccount && (!isset($bankBook->adjust_balance) || $bankBook->adjust_balance)) {
                if ($bankBook->type == 'Receive') {
                    $newAccount->balance += $bankBook->amount;
                } else {
                    $newAccount->balance -= $bankBook->amount;
                }
                $newAccount->save();
            }
        });

        // Deleting
        static::deleting(function ($bankBook) {
            $account = Account::find($bankBook->account_id);
            if (!$account) return;

            if (!isset($bankBook->adjust_balance) || $bankBook->adjust_balance) {
                if ($bankBook->type == 'Receive') {
                    $account->balance -= $bankBook->amount;
                } else {
                    $account->balance += $bankBook->amount;
                }
                $account->save();
            }

            Log::info('BankBook deleted', [
                'bankbook_id' => $bankBook->id,
                'account_id'  => $bankBook->account_id,
                'type'        => $bankBook->type,
                'amount'      => $bankBook->amount,
                'adjusted'    => $bankBook->adjust_balance ?? true
            ]);
        });
    }
}
