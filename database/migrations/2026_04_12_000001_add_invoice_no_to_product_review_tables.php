<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('product_reviews', 'invoice_no')) {
                $table->string('invoice_no')->nullable()->after('product_id');
                $table->unique('invoice_no');
            }
        });

        Schema::table('shoe_product_reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('shoe_product_reviews', 'invoice_no')) {
                $table->string('invoice_no')->nullable()->after('product_id');
                $table->unique('invoice_no');
            }
        });

        Schema::table('cosmetic_product_reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('cosmetic_product_reviews', 'invoice_no')) {
                $table->string('invoice_no')->nullable()->after('product_id');
                $table->unique('invoice_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('product_reviews', 'invoice_no')) {
                $table->dropUnique(['invoice_no']);
                $table->dropColumn('invoice_no');
            }
        });

        Schema::table('shoe_product_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('shoe_product_reviews', 'invoice_no')) {
                $table->dropUnique(['invoice_no']);
                $table->dropColumn('invoice_no');
            }
        });

        Schema::table('cosmetic_product_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('cosmetic_product_reviews', 'invoice_no')) {
                $table->dropUnique(['invoice_no']);
                $table->dropColumn('invoice_no');
            }
        });
    }
};

