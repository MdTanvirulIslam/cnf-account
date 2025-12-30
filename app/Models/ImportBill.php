<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'lc_no',
        'lc_date',
        'bill_no',
        'bill_date',
        'item',
        'value',
        'qty',
        'weight',
        'be_no',
        'be_date',
        'scan_fee',
        'doc_fee',
        'account_id',
        'ait_account_id',
        'port_account_id',
        'itc',

    ];

    protected $casts = [
        'lc_date'   => 'date',
        'bill_date' => 'date',
        'be_date'   => 'date',
        'value'     => 'decimal:2',
        'scan_fee'  => 'decimal:2',
        'doc_fee'   => 'decimal:2',
        'weight'    => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bill) {
            $bill->company_name = env('COMPANY_NAME', 'MULTI FABS LTD');
        });

        static::updating(function ($bill) {
            $bill->company_name = env('COMPANY_NAME', 'MULTI FABS LTD');
        });
    }

    /** ───────────────
     *  Relationships
     *  ───────────────
     */
    public function expenses()
    {
        return $this->hasMany(ImportBillExpense::class, 'import_bill_id');
    }

    /** ───────────────
     *  Helpers
     *  ───────────────
     */
    public function portBill()
    {
        return $this->getExpenseAmount('Port Bill (As Per Receipt)');
    }

    public function dfVat()
    {
        return $this->getExpenseAmount('AIT (As Per Receipt)');
    }

    public function getExpenseAmount($type)
    {
        // Use loaded collection if available (avoid N+1 queries)
        if ($this->relationLoaded('expenses')) {
            return $this->expenses
                ->where('expense_type', $type)
                ->sum('amount');
        }

        return $this->expenses()
            ->where('expense_type', $type)
            ->sum('amount');
    }

    public function totalExpenses()
    {
        return $this->expenses()->sum('amount');
    }
}
