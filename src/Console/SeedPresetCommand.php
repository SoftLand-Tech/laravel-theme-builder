<?php

declare(strict_types=1);

namespace SoftLand\ThemeBuilder\Console;

use Illuminate\Console\Command;
use SoftLand\ThemeBuilder\Seeders\ThemePresetSeeder;

class SeedPresetCommand extends Command
{
    protected $signature = 'theme-builder:seed-preset {slug=default : The preset slug (JSON file basename)}';

    protected $description = 'Seed a theme-builder preset into the themes table';

    public function handle(ThemePresetSeeder $seeder): int
    {
        $slug = (string) $this->argument('slug');

        $theme = $seeder->seed($slug);

        $this->components->info("Seeded preset [{$slug}] as theme #{$theme->id}.");

        return self::SUCCESS;
    }
}
