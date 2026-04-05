<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rooms')) {
            return;
        }

        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'beds')) {
                $table->unsignedInteger('beds')->nullable()->after('room_number');
            }
            if (!Schema::hasColumn('rooms', 'services')) {
                $table->text('services')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('rooms')) {
            return;
        }

        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'beds')) {
                $table->dropColumn('beds');
            }
            if (Schema::hasColumn('rooms', 'services')) {
                $table->dropColumn('services');
            }
        });
    }
};
