<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('home_banners')) {
            return;
        }

        if (!Schema::hasColumn('home_banners', 'desktop_image_path')) {
            Schema::table('home_banners', function (Blueprint $table) {
                $table->string('desktop_image_path')->nullable()->after('video_path');
            });
        }

        if (!Schema::hasColumn('home_banners', 'mobile_image_path')) {
            Schema::table('home_banners', function (Blueprint $table) {
                $table->string('mobile_image_path')->nullable()->after('desktop_image_path');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('home_banners')) {
            return;
        }

        Schema::table('home_banners', function (Blueprint $table) {
            if (Schema::hasColumn('home_banners', 'desktop_image_path')) {
                $table->dropColumn('desktop_image_path');
            }

            if (Schema::hasColumn('home_banners', 'mobile_image_path')) {
                $table->dropColumn('mobile_image_path');
            }
        });
    }
};