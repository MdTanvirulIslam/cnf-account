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
        Schema::create('import_bill_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('import_bill_id');
            $table->string('expense_type');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
            $table->foreign('import_bill_id')
                ->references('id')
                ->on('import_bills')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_bill_expenses');
    }
};
