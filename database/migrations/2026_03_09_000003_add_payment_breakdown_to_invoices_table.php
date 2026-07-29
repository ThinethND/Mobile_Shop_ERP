<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'cash_paid')) {
                $table->decimal('cash_paid', 12, 2)->default(0)->after('payment_type');
            }

            if (!Schema::hasColumn('invoices', 'card_paid')) {
                $table->decimal('card_paid', 12, 2)->default(0)->after('cash_paid');
            }

            if (!Schema::hasColumn('invoices', 'advance_amount')) {
                $table->decimal('advance_amount', 12, 2)->default(0)->after('card_paid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'advance_amount')) {
                $table->dropColumn('advance_amount');
            }

            if (Schema::hasColumn('invoices', 'card_paid')) {
                $table->dropColumn('card_paid');
            }

            if (Schema::hasColumn('invoices', 'cash_paid')) {
                $table->dropColumn('cash_paid');
            }
        });
    }
};