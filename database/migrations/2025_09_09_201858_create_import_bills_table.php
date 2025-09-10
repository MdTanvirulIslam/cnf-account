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
        Schema::create('import_bills', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('lc_no')->nullable();
            $table->date('lc_date')->nullable();
            $table->string('bill_no')->nullable();
            $table->date('bill_date')->nullable();
            $table->string('item')->nullable();
            $table->decimal('value', 15, 2)->default(0);
            $table->integer('qty')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('be_no')->nullable();
            $table->date('be_date')->nullable();
            $table->decimal('scan_fee', 15, 2)->default(0);
            $table->decimal('doc_fee', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_bills');
    }
};
