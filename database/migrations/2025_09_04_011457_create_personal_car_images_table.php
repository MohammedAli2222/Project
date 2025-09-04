<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('personal_car_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_car_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->boolean('is_main')->default(false);
            $table->timestamps();

            $table->index(['personal_car_id', 'is_main']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_car_images');
    }
};
