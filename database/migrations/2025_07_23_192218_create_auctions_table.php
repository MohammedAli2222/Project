<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->foreignId('showroom_id')->constrained()->onDelete('cascade');
            $table->decimal('starting_price', 10, 2);
            $table->decimal('current_price', 10, 2)->default(0);
            $table->decimal('minimum_increment', 10, 2)->default(10);
            $table->foreignId('winner_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', ['upcoming', 'active', 'ended', 'cancelled'])->default('upcoming');

            $table->datetime('start_time');
            $table->datetime('end_time');

            $table->boolean('extend_on_last_minute')->default(true);
            $table->datetime('extended_until')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
