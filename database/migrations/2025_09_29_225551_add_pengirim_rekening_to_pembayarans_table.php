<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('nama_rekening_pengirim')->nullable()->after('wali_bank_id');
            $table->string('nomor_rekening_pengirim')->nullable()->after('nama_rekening_pengirim');
            $table->string('nama_bank_pengirim')->nullable()->after('nomor_rekening_pengirim');
            $table->string('kode_bank_pengirim')->nullable()->after('nama_bank_pengirim');
        });
    }

    public function down()
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn([
                'nama_rekening_pengirim',
                'nomor_rekening_pengirim',
                'nama_bank_pengirim',
                'kode_bank_pengirim'
            ]);
        });
    }
};
