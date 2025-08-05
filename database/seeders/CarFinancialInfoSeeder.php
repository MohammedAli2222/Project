<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarFinancialInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // افترض أن لدينا 50 سيارة تم إنشاؤها بالفعل بواسطة CarSeeder
        for ($i = 1; $i <= 50; $i++) {
            $negotiable = random_int(0, 1);
            $discountPercentage = null;
            $discountAmount = null;
            $price = random_int(5000000, 100000000); // مثال: 5,000,000 إلى 100,000,000 ليرة سورية

            if (random_int(0, 1)) { // 50% chance of having a discount
                $discountPercentage = round(random_int(500, 2000) / 100, 2); // 5.00% to 20.00%
                $discountAmount = round($price * ($discountPercentage / 100), 2);
            }

            DB::table('car_financial_infos')->insert([
                'car_id' => $i,
                'price' => $price,
                'currency' => collect(['SYP', 'USD', 'EUR'])->random(),
                'negotiable' => $negotiable,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
