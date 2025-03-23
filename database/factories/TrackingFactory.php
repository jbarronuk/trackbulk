<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Tracking;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrackingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tracking::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'number' => $this->faker->uuid, // Generates a random UUID for the number
            'type' => $this->faker->numberBetween(1, 5), // Random type between 1 and 5
            'status' => $this->faker->optional()->numberBetween(1, 10), // Random or null status
            'response' => $this->faker->optional()->sentence, // Random sentence or null
            'account_id' => Account::factory(), // Creates a related Account record
        ];
    }
}
