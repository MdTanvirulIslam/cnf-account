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
        'account_id','type','amount','note'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    protected static function booted()
    {
        static::creating(function ($bankBook) {

            DB::transaction(function () use ($bankBook) {
                $account = $bankBook->account;

                // Check balance for withdraw or pay order
                if(in_array($bankBook->type, ['Withdraw','Pay Order']) && $bankBook->amount > $account->balance){
                    throw new \Exception("Insufficient account balance!");
                }

                // Adjust balance
                if($bankBook->type == 'Receive'){
                    $account->balance += $bankBook->amount;
                } else {
                    $account->balance -= $bankBook->amount;
                }

                $account->save();

                Log::info('BankBook created', [
                    'bankbook_id'=>$bankBook->id,
                    'account_id'=>$account->id,
                    'type'=>$bankBook->type,
                    'amount'=>$bankBook->amount
                ]);
            });
        });

        static::updating(function ($bankBook) {

            DB::transaction(function () use ($bankBook) {

                $original = $bankBook->getOriginal();

                $oldAccount = Account::find($original['account_id']);
                if($original['type'] == 'Receive'){
                    $oldAccount->balance -= $original['amount'];
                } else {
                    $oldAccount->balance += $original['amount'];
                }
                $oldAccount->save();

                $newAccount = $bankBook->account;

                // Check balance for withdraw or pay order
                if(in_array($bankBook->type, ['Withdraw','Pay Order']) && $bankBook->amount > $newAccount->balance){
                    throw new \Exception("Insufficient account balance!");
                }

                if($bankBook->type == 'Receive'){
                    $newAccount->balance += $bankBook->amount;
                } else {
                    $newAccount->balance -= $bankBook->amount;
                }

                $newAccount->save();

                Log::info('BankBook updated', [
                    'bankbook_id'=>$bankBook->id,
                    'old_account_id'=>$original['account_id'],
                    'new_account_id'=>$bankBook->account_id,
                    'type'=>$bankBook->type,
                    'amount'=>$bankBook->amount
                ]);

            });
        });
    }
}
