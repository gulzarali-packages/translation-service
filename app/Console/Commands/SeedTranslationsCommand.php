<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:seed {count=100000 : Number of translations to seed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with a large number of translations for performance testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->argument('count');
        $this->info("Seeding {$count} translations...");

        // Start timing
        $startTime = microtime(true);

        // Disable query log to save memory
        DB::disableQueryLog();

        // Create languages if they don't exist
        $this->createLanguages();

        // Create tags if they don't exist
        $this->createTags();

        // Get all language IDs
        $languageIds = Language::pluck('id')->toArray();
        
        // Get all tag IDs
        $tagIds = Tag::pluck('id')->toArray();

        // Seed translations in chunks to avoid memory issues
        $chunkSize = 1000;
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i += $chunkSize) {
            $this->seedTranslationsChunk(min($chunkSize, $count - $i), $languageIds, $tagIds);
            $bar->advance($chunkSize);
        }

        $bar->finish();
        $this->newLine();

        // Calculate and display execution time
        $executionTime = microtime(true) - $startTime;
        $this->info("Seeded {$count} translations in {$executionTime} seconds");
    }

    /**
     * Create default languages.
     */
    private function createLanguages(): void
    {
        $languages = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'fr', 'name' => 'French'],
            ['code' => 'es', 'name' => 'Spanish'],
            ['code' => 'de', 'name' => 'German'],
            ['code' => 'it', 'name' => 'Italian'],
        ];

        foreach ($languages as $language) {
            Language::firstOrCreate(['code' => $language['code']], $language);
        }

        $this->info('Languages created successfully');
    }

    /**
     * Create default tags.
     */
    private function createTags(): void
    {
        $tags = [
            ['name' => 'mobile', 'description' => 'Mobile applications'],
            ['name' => 'desktop', 'description' => 'Desktop applications'],
            ['name' => 'web', 'description' => 'Web applications'],
            ['name' => 'api', 'description' => 'API responses'],
            ['name' => 'error', 'description' => 'Error messages'],
            ['name' => 'success', 'description' => 'Success messages'],
            ['name' => 'notification', 'description' => 'Notification messages'],
            ['name' => 'email', 'description' => 'Email content'],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(['name' => $tag['name']], $tag);
        }

        $this->info('Tags created successfully');
    }

    /**
     * Seed a chunk of translations.
     *
     * @param int $count
     * @param array $languageIds
     * @param array $tagIds
     */
    private function seedTranslationsChunk(int $count, array $languageIds, array $tagIds): void
    {
        // Common translation key prefixes
        $keyPrefixes = [
            'common', 'auth', 'validation', 'errors', 'success',
            'buttons', 'labels', 'placeholders', 'titles', 'messages',
            'navigation', 'footer', 'header', 'sidebar', 'dashboard'
        ];

        // Use DB transaction for better performance
        DB::beginTransaction();

        try {
            $translationData = [];
            $translationTagData = [];
            $now = now();

            for ($i = 0; $i < $count; $i++) {
                $languageId = $languageIds[array_rand($languageIds)];
                $keyPrefix = $keyPrefixes[array_rand($keyPrefixes)];
                $key = "{$keyPrefix}.key_" . uniqid();
                
                // Create translation
                $translation = Translation::create([
                    'language_id' => $languageId,
                    'key' => $key,
                    'content' => "Translation content for {$key}",
                    'metadata' => json_encode([
                        'context' => "Context for {$key}",
                        'max_length' => rand(10, 500),
                        'last_edited_by' => "user" . rand(1, 10) . "@example.com",
                    ]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Attach random tags (1-3 tags per translation)
                $tagCount = rand(1, 3);
                $selectedTagIds = array_rand(array_flip($tagIds), $tagCount);
                
                if (!is_array($selectedTagIds)) {
                    $selectedTagIds = [$selectedTagIds];
                }
                
                foreach ($selectedTagIds as $tagId) {
                    $translation->tags()->attach($tagId);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error seeding translations: {$e->getMessage()}");
        }
    }
}
