<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash; // تأكد من استيراد الـ Hash facade

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // ستحتاج إلى التأكد من وجود أدوار (roles) في جدول roles
        // افترض أن لديك role_id = 1 للدور الافتراضي
        // يمكنك تعديل هذا ليناسب منطق أدوارك
        $roleId = \App\Models\Role::inRandomOrder()->first()->id ?? 1; // الحصول على role_id موجود أو افتراضي

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'user_name' => fake()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->phoneNumber(),
            'password' => Hash::make('12345678'), // كلمة المرور المطلوبة
            'profile_picture' => null, // أو fake()->imageUrl() إذا أردت صورًا عشوائية
            'role_id' => $roleId,
            'remember_token' => Str::random(10),
        ];
    }
}
