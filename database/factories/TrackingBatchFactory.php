<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\TrackingBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrackingBatchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TrackingBatch::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'account_id' => Account::factory(),
        ];
    }
}
