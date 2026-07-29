<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'delivery_enabled')) {
                $table->boolean('delivery_enabled')->nullable()->after('ship_via');
            }

            if (!Schema::hasColumn('invoices', 'delivery_method')) {
                $table->string('delivery_method')->nullable()->after('delivery_enabled');
            }

            if (!Schema::hasColumn('invoices', 'delivery_payment_status')) {
                $table->string('delivery_payment_status')->nullable()->after('delivery_method');
            }

            if (!Schema::hasColumn('invoices', 'tracking_id')) {
                $table->string('tracking_id')->nullable()->after('delivery_payment_status');
            }

            if (!Schema::hasColumn('invoices', 'delivery_agent')) {
                $table->string('delivery_agent')->nullable()->after('tracking_id');
            }

            if (!Schema::hasColumn('invoices', 'delivery_amount')) {
                $table->decimal('delivery_amount', 12, 2)->nullable()->after('delivery_agent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $columns = [
                'delivery_enabled',
                'delivery_method',
                'delivery_payment_status',
                'tracking_id',
                'delivery_agent',
                'delivery_amount',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};