<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'stripe' => 'price_' . $this->faker->unique()->bothify('??##??##??##'),
            'quota' => $this->faker->numberBetween(10, 100),
            'status' => 1,
        ];
    }
}