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
        Schema::table('import_bills', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->after('id');
            $table->unsignedBigInteger('ait_account_id')->nullable()->after('account_id');
            $table->unsignedBigInteger('port_account_id')->nullable()->after('ait_account_id');


            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->foreign('ait_account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->foreign('port_account_id')->references('id')->on('accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_bills', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropForeign(['ait_account_id']);
            $table->dropForeign(['port_account_id']);

            $table->dropColumn(['account_id', 'ait_account_id', 'port_account_id']);
        });
    }
};
