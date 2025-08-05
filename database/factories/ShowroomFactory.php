<?php

namespace Database\Factories;

use App\Models\Showroom;
use App\Models\User; // استيراد نموذج المستخدم
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Showroom>
 */
class ShowroomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Showroom::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // نحتاج إلى التأكد من أننا نربط المعرض بمستخدم موجود.
        // يمكننا استخدام find(1) إذا كنا متأكدين أن المستخدم رقم 1 موجود
        // أو استخدام inRandomOrder()->first()->id للحصول على معرف مستخدم عشوائي موجود.
        // الأفضل هو التأكد من أن المستخدمين الذين يملكون الأدوار الصحيحة موجودون
        // قبل إنشاء المعارض.

        // هنا سنفترض أن هناك مستخدمين موجودين بالفعل يمكنهم امتلاك معارض.
        // يمكنك تعديل هذا ليناسب منطق أدوارك.
        $userId = User::inRandomOrder()->first()->id;

        return [
            'user_id' => $userId,
            'name' => fake()->company() . ' Showroom', // اسم شركة وهمية للمعرض
            'location' => fake()->address(),
            'logo' => null, // يمكنك استخدام fake()->imageUrl() إذا أردت شعارات وهمية
            'phone' => fake()->numerify('##########'), // رقم هاتف من 10 أرقام
        ];
    }
}
