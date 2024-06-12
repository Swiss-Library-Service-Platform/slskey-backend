<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use jeremykenedy\LaravelRoles\Models\Permission;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_identifier' => $this->faker->uuid(),
            'display_name' => $this->faker->name(),
            'is_edu_id' => $this->faker->boolean(),
            'alma_username' => $this->faker->userName(),
            'alma_institution' => $this->faker->company(),
            'password' => $this->faker->password(),
            'password_change_at' => $this->faker->dateTime(),
        ];
    }

    public function edu_id()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_edu_id' => true,
            ];
        });
    }

    public function non_edu_id_password_changed()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_edu_id' => false,
                'password_change_at' => $this->faker->dateTime(),
            ];
        });
    }

    public function non_edu_id_password_unchanged()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_edu_id' => false,
                'password_change_at' => null,
            ];
        });
    }

    public function withPermissions(...$permissions)
    {
        // If only one argument is passed and it's an array, extract permissions from it
        if (count($permissions) === 1 && is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

        // attach each permission
        return $this->afterCreating(function (User $user) use ($permissions) {
            foreach ($permissions as $perm) {
                $user->givePermissions($perm);
            }
        });
    }

    public function withRandomPermissions(int $count)
    {
        // attach each permission
        return $this->afterCreating(function (User $user) use ($count) {
            $permissions = Permission::inRandomOrder()->limit($count)->get();
            foreach ($permissions as $perm) {
                $user->givePermissions($perm->slug);
            }
        });
    }

    public function withAlmaPermissions(string $almaUsername, string $almaInstitution)
    {
        return $this->afterCreating(function (User $user) use ($almaUsername, $almaInstitution) {
            $user->alma_username = $almaUsername;
            $user->alma_institution = $almaInstitution;
            $user->save();
        });
    }
}
