<?php

namespace Database\Factories;

use App\Models\Profession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => User::factory(),
            'profession_id' => Profession::factory(),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'payout' => $this->faker->randomFloat(2, 50, 500),
        ];
    }
}
