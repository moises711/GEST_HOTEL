<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'modules')) {
                $table->json('modules')->nullable()->after('description');
            }
        });

        Schema::table('hotels', function (Blueprint $table) {
            if (!Schema::hasColumn('hotels', 'modules')) {
                $table->json('modules')->nullable()->after('database');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'modules')) {
                $table->dropColumn('modules');
            }
        });

        Schema::table('hotels', function (Blueprint $table) {
            if (Schema::hasColumn('hotels', 'modules')) {
                $table->dropColumn('modules');
            }
        });
    }
};
