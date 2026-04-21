<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        DB::table('users')->updateOrInsert(
            ['email' => 'khirviecliffordbautista@gmail.com'],
            [
                'student_id' => 'MAR000000000001',
                'password' => Hash::make('EPASe@2025'),
                'first_name' => 'Khirvie Clifford',
                'middle_name' => 'N.',
                'last_name' => 'Bautista',
                'ext_name' => '',
                'role' => 'admin',
                'department_id' => 1,
                'stat' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Instructor
        DB::table('users')->updateOrInsert(
            ['email' => 'karlrapada@gmail.com'],
            [
                'student_id' => 'MAR000000000002',
                'password' => Hash::make('EPASe@2025'),
                'first_name' => 'Karl Lynuz',
                'middle_name' => 'B.',
                'last_name' => 'Rapada',
                'ext_name' => '',
                'role' => 'instructor',
                'section' => 'S8B1',
                'department_id' => 1,
                'stat' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Students
        DB::table('users')->updateOrInsert(
            ['email' => 'mikaellayap23@gmail.com'],
            [
                'student_id' => 'MAR000000000003',
                'password' => Hash::make('EPASe@2025'),
                'first_name' => 'Mikaella Rosalia',
                'middle_name' => 'Y.',
                'last_name' => 'Torre',
                'ext_name' => '',
                'role' => 'student',
                'section' => 'S8A1',
                'department_id' => 1,
                'stat' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'kookyarabia06@gmail.com'],
            [
                'student_id' => 'MAR000000000004',
                'password' => Hash::make('EPASe@2025'),
                'first_name' => 'Kooky Lyann',
                'middle_name' => '',
                'last_name' => 'Arabia',
                'ext_name' => '',
                'role' => 'student',
                'section' => 'S8A1',
                'department_id' => 1,
                'stat' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'sheilamerida@gmail.com'],
            [
                'student_id' => 'MAR000000000005',
                'password' => Hash::make('EPASe@2025'),
                'first_name' => 'Sheila Marie',
                'middle_name' => 'M.',
                'last_name' => 'Merida',
                'ext_name' => '',
                'role' => 'student',
                'section' => 'S8B1',
                'department_id' => 1,
                'stat' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Instructor - S8A1
        DB::table('users')->updateOrInsert(
            ['email' => 'KebinSy2121252@gmail.com'],
            [
                'student_id' => 'MAR000000000006',
                'password' => Hash::make('EPASe@2025'),
                'first_name' => 'Andrei Kevin',
                'middle_name' => 'A.',
                'last_name' => 'Sy',
                'ext_name' => '',
                'role' => 'instructor',
                'section' => 'S8A1',
                'department_id' => 1,
                'stat' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
