<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CarGeneralInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            DB::table('car_general_infos')->insert([
                'car_id'    => $i,
                'condition' => collect(['new', 'used'])->random(),
                'vin'       => strtoupper(Str::random(17)), // رقم VIN فريد
                'name'      => 'Car ' . $i . ' Name',
                'brand'     => collect(['Toyota', 'Honda', 'BMW', 'Mercedes-Benz', 'Audi', 'Ford', 'Chevrolet'])->random(),
                'model'     => collect(['Camry', 'Civic', 'X5', 'C-Class', 'A4', 'F-150', 'Malibu'])->random(),
                'gear_box'  => collect(['manual', 'automatic', 'cvt'])->random(),
                'year'      => random_int(2000, 2024),
                'fuel_type' => collect(['petrol', 'diesel', 'hybrid', 'electric'])->random(),
                'body_type' => collect(['sedan', 'suv', 'hatchback', 'coupe', 'convertible', 'truck'])->random(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
