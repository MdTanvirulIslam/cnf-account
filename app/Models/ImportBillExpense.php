<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportBillExpense extends Model
{
    use HasFactory;

    protected $fillable = ['import_bill_id','expense_type','amount'];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function bill()
    {
        return $this->belongsTo(ImportBill::class, 'import_bill_id');
    }
}
