<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bank_books', function (Blueprint $table) {
            // If column exists, drop foreign key safely
            if (Schema::hasColumn('bank_books', 'account_id')) {
                try {
                    $table->dropForeign(['account_id']);
                } catch (\Throwable $e) {
                    // Foreign key does not exist, skip
                }

                $table->dropColumn('account_id');
            }
        });

        Schema::table('bank_books', function (Blueprint $table) {
            // Recreate with proper FK
            $table->unsignedBigInteger('account_id')->after('id');
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('bank_books', function (Blueprint $table) {
            try {
                $table->dropForeign(['account_id']);
            } catch (\Throwable $e) {
                // skip if missing
            }
            $table->dropColumn('account_id');
        });
    }
};
