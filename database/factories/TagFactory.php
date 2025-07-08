<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Common tag categories for translations
        $tagCategories = [
            'mobile', 'desktop', 'web', 'api', 'frontend', 
            'backend', 'marketing', 'legal', 'technical', 'ui', 
            'ux', 'error', 'success', 'notification', 'email'
        ];

        return [
            'name' => $this->faker->unique()->randomElement($tagCategories) . '-' . $this->faker->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
