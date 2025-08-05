<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('role')->default('User');
            $table->timestamps();
        });

        DB::table('roles')->insert([
            ['id' => 1, 'role' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'role' => 'OfficeOwner', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'role' => 'User', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
