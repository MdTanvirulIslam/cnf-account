<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ExportBill;

class Buyer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'email', 'phone', 'company', 'address'];

    protected $dates = ['deleted_at'];

    public function exportBills()
    {
        return $this->hasMany(ExportBill::class);
    }
}
