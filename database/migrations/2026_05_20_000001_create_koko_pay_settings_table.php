<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('koko_pay_settings')) {
            Schema::create('koko_pay_settings', function (Blueprint $table) {
                $table->id();
                $table->decimal('percentage', 5, 2)->default(0);
                $table->timestamps();
            });
        }

        if (DB::table('koko_pay_settings')->where('id', 1)->doesntExist()) {
            DB::table('koko_pay_settings')->insert([
                'id' => 1,
                'percentage' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('koko_pay_settings');
    }
};
