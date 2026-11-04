<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('kelas', 10)->change();
        });
    }

    public function down(): void
    {
        if (DB::table('siswas')->whereRaw('CHAR_LENGTH(kelas) > 3')->exists()) {
            throw new \RuntimeException('Cannot shrink siswas.kelas while class values are longer than three characters.');
        }

        Schema::table('siswas', function (Blueprint $table) {
            $table->string('kelas', 3)->change();
        });
    }
};
