<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Slides\Saml2\Models\Tenant;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class Saml2TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid(),
            'key' => $this->faker->word(),
            'idp_entity_id' => $this->faker->url(),
            'idp_login_url' => $this->faker->url(),
            'idp_logout_url' => $this->faker->url(),
            'idp_x509_cert' => $this->faker->word(),
            'relay_state_url' => $this->faker->url(),
            'name_id_format' => $this->faker->word(),
            'metadata' => $this->faker->word(),
        ];
    }

    public function eduid()
    {
        return $this->state(function (array $attributes) {
            return [
                'key' => 'eduid',
            ];
        });
    }
}
