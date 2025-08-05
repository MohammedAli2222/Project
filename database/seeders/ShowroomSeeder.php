<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Showroom; // استيراد نموذج Showroom
use App\Models\User; // استيراد نموذج User (مطلوب للتأكد من وجود مالك للمعارض)

class ShowroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تأكد من وجود مستخدمين قبل إنشاء المعارض.
        // إذا كنت قد قمت بتشغيل UserSeeder، فهذا جيد.
        // يمكنك هنا تحديد المستخدمين الذين يمكنهم امتلاك معارض (مثلاً، أول 10 مستخدمين)
        // أو إنشاء مستخدمين جدد لهذا الغرض.

        // مثال: إنشاء معرضين لكل مستخدم من أول 10 مستخدمين (إذا كان UserSeeder قد تم تشغيله بالفعل)
        $users = User::take(10)->get();

        foreach ($users as $user) {
            Showroom::factory()->count(1)->create([
                'user_id' => $user->id,
                'name' => $user->user_name . "'s Showroom", // اسم مخصص بناءً على المستخدم
                'location' => fake()->city(), // موقع عشوائي للملء السريع
            ]);
        }

        // أو يمكنك إنشاء عدد محدد من المعارض بشكل عام (سيرتبطون بمستخدمين عشوائيين)
        // Showroom::factory()->count(20)->create();
    }
}
