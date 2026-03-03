<?php

namespace Database\Factories;

use App\Models\InstagramAccessToken;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstagramAccessTokenFactory extends Factory
{
    protected $model = InstagramAccessToken::class;

    public function definition(): array
    {
        return [
            'access_token' => encrypt('fake-access-token-'.$this->faker->uuid()),
            'expires_at' => now()->addMonths(3),
        ];
    }
}
