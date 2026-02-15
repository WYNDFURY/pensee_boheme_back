<?php

namespace Database\Factories;

use App\Models\InstagramMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstagramMediaFactory extends Factory
{
    protected $model = InstagramMedia::class;

    public function definition(): array
    {
        return [
            'media_id' => $this->faker->unique()->uuid(),
            'caption' => $this->faker->sentence(),
            'media_type' => $this->faker->randomElement(['IMAGE', 'CAROUSEL_ALBUM']),
            'media_url' => $this->faker->url(),
            'permalink' => $this->faker->url(),
            'timestamp' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
