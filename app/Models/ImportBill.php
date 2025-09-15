<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name','lc_no','lc_date','bill_no','bill_date',
        'item','value','qty','weight','be_no','be_date',
        'scan_fee','doc_fee'
    ];

    protected $casts = [
        'lc_date'  => 'date',
        'bill_date'=> 'date',
        'be_date'  => 'date',
        'value'    => 'decimal:2',
        'scan_fee' => 'decimal:2',
        'doc_fee'  => 'decimal:2',
        'weight'   => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto set company_name when creating
        static::creating(function ($bill) {
            $bill->company_name = env('COMPANY_NAME');
        });

        // Auto set company_name when updating
        static::updating(function ($bill) {
            $bill->company_name = env('COMPANY_NAME');
        });
    }

    public function expenses()
    {
        return $this->hasMany(ImportBillExpense::class, 'import_bill_id');
    }
}
