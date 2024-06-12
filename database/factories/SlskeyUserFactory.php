<?php

namespace Database\Factories;

use App\Models\SlskeyUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SlskeyUser>
 */
class SlskeyUserFactory extends Factory
{
    protected $model = SlskeyUser::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'primary_id' => $this->faker->unique()->numberBetween(1000000000, 9999999999).'@eduid.ch',
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
        ];
    }
}
