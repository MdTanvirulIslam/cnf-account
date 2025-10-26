<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExportBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'buyer_id',
        'invoice_no',
        'invoice_date',
        'bill_no',
        'bill_date',
        'usd',
        'total_qty',
        'ctn_no',
        'be_no',
        'be_date',
        'qty_pcs',
        'from_account_id',
        'account_id',
        'note'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'bill_date' => 'date',
        'be_date' => 'date',
        'usd' => 'decimal:2',
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
     * Relationships
     * ───────────────
     */
    public function expenses()
    {
        return $this->hasMany(ExportBillExpense::class, 'export_bill_id');
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    /** ───────────────
     * Helpers
     * ───────────────
     */
    // Sum of all expenses
    public function submittedExpense()
    {
        return $this->expenses()->sum('amount');
    }

// DF VAT from specific expense_type
    public function dfVat()
    {
        return $this->getExpenseAmount('Bank C & F Vat & Others (As Per Receipt)');
    }

// Helper
    public function getExpenseAmount($type)
    {
        if ($this->relationLoaded('expenses')) {
            return $this->expenses->where('expense_type', $type)->sum('amount');
        }

        return $this->expenses()->where('expense_type', $type)->sum('amount');
    }
}
