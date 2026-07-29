<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('invoices', 'order_status')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('order_status', 30)->default('reserved')->after('status');
            });
        }

        DB::table('invoices')
            ->whereNull('order_status')
            ->update(['order_status' => 'reserved']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'order_status')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('order_status');
            });
        }
    }
};
