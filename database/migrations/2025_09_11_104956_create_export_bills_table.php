<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('export_bills', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->foreignId('buyer_id')->constrained('buyers')->onDelete('cascade');
            $table->string('invoice_no');
            $table->date('invoice_date')->nullable();
            $table->string('bill_no');
            $table->date('bill_date')->nullable();
            $table->decimal('usd', 15, 2)->default(0);
            $table->integer('total_qty')->default(0);
            $table->string('ctn_no')->nullable();
            $table->string('be_no')->nullable();
            $table->date('be_date')->nullable();
            $table->integer('qty_pcs')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_bills');
    }
};
