<?php

namespace Database\Factories;

use App\Models\Cow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cow>
 */
class CowFactory extends Factory
{
    protected $model = Cow::class;

    public function definition()
    {
        $breeds = ['Holstein', 'Jersey', 'Guernsey', 'Brown Swiss', 'Ayrshire'];

        return [
            'name' => $this->faker->firstName.' Cow',
            'tag_number' => (string) $this->faker->unique()->numberBetween(10000, 99999),
            'breed' => $this->faker->randomElement($breeds),
            'dob' => $this->faker->dateTimeBetween('-5 years', '-6 months')->format('Y-m-d'),
            'weight_kg' => $this->faker->randomFloat(2, 200.0, 900.0),
            'notes' => $this->faker->optional()->sentence(),
            'meta' => [
                'source' => 'seed',
                'health' => $this->faker->randomElement(['healthy', 'ill', 'recovering']),
            ],
        ];
    }
}
