<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('car_general_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained(table: 'cars')->onDelete('cascade');
            $table->enum('condition', ['new', 'used']);
            $table->string('vin', 17)->unique();
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
            ]); // يمكنك إزالة ->nullable() إذا كنت تريد إجبارياً
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_general_infos');
    }
};
