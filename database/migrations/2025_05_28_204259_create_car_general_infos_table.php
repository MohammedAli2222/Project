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
             $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->string('brand');
            $table->string('model');
            $table->year('year');
            $table->enum('gearbox', ['manual', 'automatic']);
            $table->enum('fuel_type', ['gasoline', 'diesel', 'hybrid', 'electric']);
            $table->string('body_type')->nullable();
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
