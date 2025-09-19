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
        Schema::table('export_bills', function (Blueprint $table) {
            if (!Schema::hasColumn('export_bills', 'from_account_id')) {
                $table->unsignedBigInteger('from_account_id')->nullable()->after('id');
            }

            $table->foreign('from_account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('export_bills', function (Blueprint $table) {
            if (Schema::hasColumn('export_bills', 'from_account_id')) {
                $table->dropForeign(['from_account_id']);
                $table->dropColumn('from_account_id');
            }
        });
    }
};
