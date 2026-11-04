<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->foreignId('wali_bank_id')->nullable()->constrained('banks')->nullOnDelete()->after('id');
            $table->foreignId('bank_sekolah_id')->nullable()->constrained('banks')->nullOnDelete()->after('id');
        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropForeign(['wali_bank_id']);
            $table->dropForeign(['bank_sekolah_id']);
            $table->dropColumn(['wali_bank_id', 'bank_sekolah_id']);
        });
    }
};
