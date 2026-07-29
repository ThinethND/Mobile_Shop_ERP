<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cosmetic_products', 'slug')) {
            Schema::table('cosmetic_products', function (Blueprint $table) {
                $table->string('slug')->nullable()->unique();
            });
        }

        DB::table('cosmetic_products')
            ->select(['id', 'name'])
            ->whereNull('slug')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $base = Str::slug((string) $row->name);
                    $base = $base !== '' ? substr($base, 0, 220) : 'cosmetic-product';

                    DB::table('cosmetic_products')
                        ->where('id', $row->id)
                        ->update([
                            'slug' => $base . '-' . $row->id,
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('cosmetic_products', 'slug')) {
            return;
        }

        Schema::table('cosmetic_products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};

