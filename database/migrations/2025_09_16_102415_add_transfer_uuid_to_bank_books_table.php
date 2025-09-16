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
            $table->uuid('transfer_uuid')->nullable()->after('note')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_books', function (Blueprint $table) {
            $table->dropColumn('transfer_uuid');
        });
    }
};
