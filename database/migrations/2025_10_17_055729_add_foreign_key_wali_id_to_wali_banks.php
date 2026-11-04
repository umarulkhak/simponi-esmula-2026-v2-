<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('wali_banks', function (Blueprint $table) {
            // Tambahkan foreign key constraint
            $table->foreign('wali_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade'); // ← ini yang membuat data bank ikut terhapus
        });
    }

    public function down()
    {
        Schema::table('wali_banks', function (Blueprint $table) {
            $table->dropForeign(['wali_id']);
        });
    }
};
