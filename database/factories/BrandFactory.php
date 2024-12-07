<?php

namespace Database\Factories;

use App\Vendor\Faker\Provider\PlaceholdCoProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    public function __construct(
        $count = null,
        ?Collection $states = null,
        ?Collection $has = null,
        ?Collection $for = null,
        ?Collection $afterMaking = null,
        ?Collection $afterCreating = null,
        $connection = null,
        ?Collection $recycle = null,
        bool $expandRelationships = true
    ) {
        parent::__construct(
            $count,
            $states,
            $has,
            $for,
            $afterMaking,
            $afterCreating,
            $connection,
            $recycle,
            $expandRelationships
        );

        $this->faker->addProvider(PlaceholdCoProvider::class);
    }

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
            'brand_image' => $this->faker->randomOptionalImage(
                dir: Storage::disk('public')->path('images'),
                word: true, // true=random word
            ),
            'rating' => mt_rand(0, 10),
        ];
    }
}
