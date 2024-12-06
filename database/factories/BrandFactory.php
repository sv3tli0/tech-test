<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'brand_name' => $this->faker->company,
            // average 1/3 chance for both strings + 1/3 for null.
            'brand_image' => $this->faker->optional(
                1/3, $this->faker->optional(1/2)->name,
            )->imageUrl,
            'rating' => mt_rand(0,10),
        ];
    }
}
