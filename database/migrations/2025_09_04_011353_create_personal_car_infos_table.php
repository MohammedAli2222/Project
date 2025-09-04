<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('personal_car_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_car_id')->constrained()->cascadeOnDelete();

            // عامة
            $table->string('name');
            $table->string('brand');
            $table->string('model');
            $table->enum('gear_box', ['manual', 'automatic', 'cvt']);
            $table->year('year');
            $table->enum('fuel_type', ['petrol', 'diesel', 'hybrid', 'electric']);
            $table->enum('body_type', ['sedan', 'suv', 'hatchback', 'coupe', 'convertible', 'truck']);
            $table->enum('color', [
                'White',
                'Grey',
                'Black',
                'Light Red',
                'Red',
                'Dark Red',
                'Light Blue',
                'Blue',
                'Dark Blue',
                'Light Green',
                'Green',
                'Dark Green',
                'Light Pink',
                'Pink',
                'Dark Pink',
                'Light Purple',
                'Purple',
                'Dark Purple',
                'Light Yellow',
                'Yellow',
                'Dark Yellow',
                'Beige',
                'Light Orange',
                'Orange',
                'Brown'
            ]);

            // تقنية
            $table->string('engine_type');
            $table->unsignedInteger('cylinders');
            $table->unsignedInteger('horse_power');

            // مالية
            $table->decimal('price', 12, 2);
            $table->enum('currency', ['SYR', 'USD']);
            $table->boolean('negotiable')->default(false);
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->nullable();

            $table->timestamps();
            $table->unique('personal_car_id'); // كل سيارة لها سجل معلومات واحد
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_car_infos');
    }
};
