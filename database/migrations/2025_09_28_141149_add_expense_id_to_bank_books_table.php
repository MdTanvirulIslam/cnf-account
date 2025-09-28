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
        Schema::table('bank_books', function (Blueprint $table) {
            $table->unsignedBigInteger('expense_id')->nullable()->after('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_books', function (Blueprint $table) {
            $table->dropColumn('expense_id');
        });
    }
};
