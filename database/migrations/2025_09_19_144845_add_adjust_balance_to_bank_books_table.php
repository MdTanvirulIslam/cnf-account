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
            $table->boolean('adjust_balance')->default(true)->after('transfer_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_books', function (Blueprint $table) {
            $table->dropColumn('adjust_balance');
        });
    }
};
