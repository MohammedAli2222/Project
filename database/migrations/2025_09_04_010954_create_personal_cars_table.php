<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('personal_cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('condition', ['new', 'used']);
            $table->string('vin', 11)->unique();
            $table->enum('available_status', ['available', 'reserved', 'sold', 'rented'])->default('available');
            $table->boolean('is_rentable')->default(false);
            $table->decimal('rental_cost_per_hour', 8, 2)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'available_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_cars');
    }
};
