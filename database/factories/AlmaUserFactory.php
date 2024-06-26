<?php

namespace Database\Factories;

use App\Enums\AlmaEnums;
use App\Models\AlmaUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AlmaUser>
 */
class AlmaUserFactory extends Factory
{
    protected $model = AlmaUser::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'primary_id' => $this->faker->word(),
            'first_name' => $this->faker->word(),
            'last_name' => $this->faker->word(),
            'full_name' => $this->faker->word(),
            'preferred_language' => $this->faker->word(),
            'preferred_email' => $this->faker->word(),
            'addresses' => $this->faker->word(),
            'user_identifier' => $this->faker->word(),
            'user_group' => $this->faker->word(),
            'email' => $this->faker->word(),
            'record_type' => AlmaEnums::RECORD_TYPE_STAFF_USER,
            'roles' => [ $this->faker->randomDigit() ]
        ];
    }
}
