<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Using 'name' to match unique constraint safely
        DB::table('departments')->updateOrInsert(
            ['name' => 'Sample Department'],
            [
                'description' => 'This is a sample department.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Call your UserSeeder
        $this->call([
            UserSeeder::class,
            DepartmentSeeder::class,
            ModuleContentSeeder::class,
            PerformanceCriteriaSeeder::class,
        ]);
    }
}
