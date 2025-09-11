<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            WorkflowSeeder::class,
            WorkflowStageSeeder::class,
            WorkflowStepSeeder::class,
            ReferralSeeder::class,
            ReferralProgressSeeder::class,
            UserSeeder::class,
        ]);
    }
}
