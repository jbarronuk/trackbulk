<?php

namespace Database\Factories;

use App\Enums\TrackingStatus;
use App\Enums\TrackingType;
use App\Models\Account;
use App\Models\Tracking;
use App\Models\TrackingBatch;
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
            'type' => TrackingType::RoyalMail,
            'status' => TrackingStatus::Unknown,
            'response' => $this->faker->optional()->sentence, // Random sentence or null
            'account_id' => Account::factory(), // Creates a related Account record
            'tracking_batch_id' => TrackingBatch::factory()
        ];
    }
}
