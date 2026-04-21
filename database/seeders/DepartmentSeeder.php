<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     *  @return void
     */
    public function run()
    {
        $departments = [
            [
                'name' => 'Instructor',
                'description' => 'Teaching and instructional department',
            ],
            [
                'name' => 'Administration',
                'description' => 'Handles administrative tasks and operations',
            ],
            [
                'name' => 'Registrar',
                'description' => 'Responsible for student records and registration',
            ],
        ];

        foreach ($departments as $dept) {
            DB::table('departments')->updateOrInsert(
                ['name' => $dept['name']],
                [
                    'description' => $dept['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
