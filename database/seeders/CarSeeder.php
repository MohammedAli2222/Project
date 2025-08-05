<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $isRentable = random_int(0, 1); // 0 or 1 for boolean
            DB::table('cars')->insert([
                'user_id' => random_int(1, 10), // افتراضيًا أن لديك 10 مستخدمين
                'showroom_id' => random_int(1, 5), // افتراضيًا أن لديك 5 معارض
                'available_status' => collect(['available', 'reserved', 'sold', 'rented'])->random(),
                'is_rentable' => $isRentable,
                'rental_cost_per_hour' => $isRentable ? round(random_int(1000, 10000) / 100, 2) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
