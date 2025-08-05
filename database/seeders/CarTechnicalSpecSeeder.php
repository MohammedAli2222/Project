<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarTechnicalSpecSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // افترض أن لدينا 50 سيارة تم إنشاؤها بالفعل بواسطة CarSeeder
        for ($i = 1; $i <= 50; $i++) {
            DB::table('car_technical_specs')->insert([
                'car_id' => $i,
                'horse_power' => random_int(80, 500),
                'engine_type' => collect(['V6', 'V8', 'Inline-4', 'Inline-6', 'Electric'])->random(),
                'cylinders' => collect([3, 4, 5, 6, 8])->random(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
