<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('delivery_charge_settings')) {
            return;
        }

        Schema::create('delivery_charge_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('cash_on_delivery_fee', 12, 2)->default(450);
            $table->decimal('bank_transfer_delivery_fee', 12, 2)->default(450);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_charge_settings');
    }
};
