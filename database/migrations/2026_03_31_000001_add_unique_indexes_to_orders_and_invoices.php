<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!$this->hasUniqueIndexOnColumn('invoices', 'invoice_no')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unique('invoice_no', 'invoices_invoice_no_unique');
            });
        }

        if (!$this->hasUniqueIndexOnColumn('orders', 'order_number')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unique('order_number', 'orders_order_number_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->hasIndexByName('invoices', 'invoices_invoice_no_unique')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropUnique('invoices_invoice_no_unique');
            });
        }

        if ($this->hasIndexByName('orders', 'orders_order_number_unique')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropUnique('orders_order_number_unique');
            });
        }
    }

    private function hasUniqueIndexOnColumn(string $table, string $column): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn (array $index) => ($index['unique'] ?? false) && ($index['columns'] ?? []) === [$column]);
    }

    private function hasIndexByName(string $table, string $indexName): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn (array $index) => ($index['name'] ?? null) === $indexName);
    }
};
