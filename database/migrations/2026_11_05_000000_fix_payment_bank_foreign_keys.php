<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropForeign('pembayarans_wali_bank_id_foreign');
            $table->dropForeign('pembayarans_bank_sekolah_id_foreign');
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->foreign('wali_bank_id')
                ->references('id')
                ->on('wali_banks')
                ->nullOnDelete();
            $table->foreign('bank_sekolah_id')
                ->references('id')
                ->on('bank_sekolahs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropForeign('pembayarans_wali_bank_id_foreign');
            $table->dropForeign('pembayarans_bank_sekolah_id_foreign');
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->foreign('wali_bank_id')
                ->references('id')
                ->on('banks')
                ->nullOnDelete();
            $table->foreign('bank_sekolah_id')
                ->references('id')
                ->on('banks')
                ->nullOnDelete();
        });
    }
};
