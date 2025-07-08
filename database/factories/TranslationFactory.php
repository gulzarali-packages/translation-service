<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Common translation key prefixes
        $keyPrefixes = [
            'common', 'auth', 'validation', 'errors', 'success',
            'buttons', 'labels', 'placeholders', 'titles', 'messages',
            'navigation', 'footer', 'header', 'sidebar', 'dashboard'
        ];

        // Generate a structured key like "common.welcome" or "errors.not_found"
        $key = $this->faker->randomElement($keyPrefixes) . '.' . $this->faker->word();

        return [
            'language_id' => Language::factory(),
            'key' => $key,
            'content' => $this->faker->paragraph(),
            'metadata' => [
                'context' => $this->faker->sentence(),
                'max_length' => $this->faker->numberBetween(10, 500),
                'last_edited_by' => $this->faker->email(),
            ],
        ];
    }
}
