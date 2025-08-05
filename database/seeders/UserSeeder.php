<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // تأكد من استيراد نموذج User
use App\Models\Role; // تأكد من استيراد نموذج Role إذا كنت تستخدمه

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تأكد من وجود أدوار قبل إنشاء المستخدمين
        // إذا لم يكن لديك أدوار بعد، يمكنك إنشاء بعضها هنا
        if (Role::count() === 0) {
            Role::create(['name' => 'admin']);
            Role::create(['name' => 'showroom_owner']);
            Role::create(['name' => 'customer']);
        }

        // إنشاء 50 مستخدمًا باستخدام Factory
        User::factory()->count(50)->create();
    }
}
