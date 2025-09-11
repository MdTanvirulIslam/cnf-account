<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExportBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name','buyer_id','invoice_no','invoice_date',
        'bill_no','bill_date','usd','total_qty','ctn_no',
        'be_no','be_date','qty_pcs'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'bill_date'    => 'date',
        'be_date'      => 'date',
        'usd'          => 'decimal:2',
    ];

    public function expenses()
    {
        return $this->hasMany(ExportBillExpense::class, 'export_bill_id');
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }
}
