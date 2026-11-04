<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->index(['siswa_id', 'tanggal_tagihan']);
            $table->index(['status', 'tanggal_tagihan']);
        });
    }

    public function down()
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropIndex(['siswa_id', 'tanggal_tagihan']);
            $table->dropIndex(['status', 'tanggal_tagihan']);
        });
    }
};
