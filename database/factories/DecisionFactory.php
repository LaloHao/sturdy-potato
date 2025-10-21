<?php

namespace Database\Factories;

use App\Models\Decision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DecisionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Decision::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(),
            'context' => $this->faker->paragraphs(2, true),
            'type' => $this->faker->randomElement(['career', 'technical', 'life', 'financial', 'startup']),
            'is_anonymous' => $this->faker->boolean(20),
            'status' => 'open',
            'expires_at' => Carbon::now()->addDays(rand(1, 30)),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    /**
     * Indicate that the decision is in draft status.
     */
    public function draft()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'draft',
            ];
        });
    }

    /**
     * Indicate that the decision is in decided status.
     */
    public function decided()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'decided',
            ];
        });
    }

    /**
     * Indicate that the decision has expired.
     */
    public function expired()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'expired',
                'expires_at' => Carbon::now()->subDays(1),
            ];
        });
    }
}
