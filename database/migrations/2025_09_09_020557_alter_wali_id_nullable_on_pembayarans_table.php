<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            // ubah wali_id jadi nullable
            $table->foreignId('wali_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            // balikin ke NOT NULL (wajib isi)
            $table->foreignId('wali_id')->nullable(false)->change();
        });
    }
};
