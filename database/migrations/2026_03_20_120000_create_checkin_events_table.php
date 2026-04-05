<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkin_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('guest_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('room_number')->nullable();
            $table->string('action', 30); // checkin|checkout|housekeeping
            $table->text('message')->nullable();
            $table->timestamp('happened_at')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'action']);
            $table->index(['hotel_id', 'guest_id']);
            $table->index(['hotel_id', 'room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkin_events');
    }
};
