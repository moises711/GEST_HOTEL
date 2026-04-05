<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'features')) {
                $table->json('features')->nullable()->after('modules');
            }
            if (!Schema::hasColumn('plans', 'max_users')) {
                $table->integer('max_users')->nullable()->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'features')) {
                $table->dropColumn('features');
            }
            if (Schema::hasColumn('plans', 'max_users')) {
                $table->dropColumn('max_users');
            }
        });
    }
};
