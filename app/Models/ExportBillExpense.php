<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExportBillExpense extends Model
{
    use HasFactory;

    protected $fillable = ['export_bill_id','expense_type','amount'];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function bill()
    {
        return $this->belongsTo(ExportBill::class, 'export_bill_id');
    }
}
