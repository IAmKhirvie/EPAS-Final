<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PopulateUsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('EPASe@2025');
        $sections = ['S8A1', 'S8B1', 'S8A2', 'S8B2', 'S9A1'];

        $firstNames = ['Juan', 'Maria', 'Jose', 'Ana', 'Carlos', 'Rosa', 'Pedro', 'Elena', 'Miguel', 'Sofia',
            'Antonio', 'Carmen', 'Rafael', 'Teresa', 'Francisco', 'Isabel', 'Manuel', 'Lucia', 'Fernando', 'Pilar',
            'Diego', 'Angela', 'Andres', 'Patricia', 'Gabriel', 'Daniela', 'Alejandro', 'Valentina', 'Mateo', 'Camila',
            'Sebastian', 'Mariana', 'Nicolas', 'Paula', 'Samuel', 'Andrea', 'Emilio', 'Natalia', 'Leonardo', 'Victoria',
            'Joaquin', 'Catalina', 'Santiago', 'Isabella', 'Tomas', 'Gabriela', 'Benjamin', 'Fernanda', 'Lucas', 'Juliana',
            'Ricardo', 'Beatriz', 'Enrique', 'Claudia', 'Gustavo', 'Monica', 'Oscar', 'Veronica', 'Raul', 'Adriana',
            'Roberto', 'Diana', 'Eduardo', 'Lorena', 'Arturo', 'Silvia', 'Hector', 'Gloria', 'Jorge', 'Alicia',
            'Marco', 'Estela', 'Luis', 'Cristina', 'Alberto'];

        $lastNames = ['Santos', 'Reyes', 'Cruz', 'Bautista', 'Garcia', 'Torres', 'Ramos', 'Mendoza', 'Rivera', 'Flores',
            'Gonzales', 'Lopez', 'Martinez', 'Rodriguez', 'Hernandez', 'De Leon', 'Villanueva', 'Castro', 'Dela Cruz', 'Soriano',
            'Pascual', 'Aquino', 'Dizon', 'Manalo', 'Salvador', 'Navarro', 'Aguilar', 'Espinosa', 'Morales', 'Castillo',
            'Mercado', 'Valdez', 'Pineda', 'Salazar', 'Romero'];

        // 25 Instructors
        for ($i = 1; $i <= 25; $i++) {
            $fn = $firstNames[array_rand($firstNames)];
            $ln = $lastNames[array_rand($lastNames)];
            $email = strtolower($fn . '.' . $ln . $i . '@instructor.epase.edu');

            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'student_id' => 'MAR' . str_pad(100 + $i, 12, '0', STR_PAD_LEFT),
                    'password' => $password,
                    'first_name' => $fn,
                    'middle_name' => chr(65 + rand(0, 25)) . '.',
                    'last_name' => $ln,
                    'ext_name' => '',
                    'role' => 'instructor',
                    'department_id' => 1,
                    'section' => $sections[array_rand($sections)],
                    'stat' => 1,
                    'email_verified_at' => now(),
                    'created_at' => now()->subDays(rand(1, 90)),
                    'updated_at' => now(),
                ]
            );
        }

        // 50 Students
        for ($i = 1; $i <= 50; $i++) {
            $fn = $firstNames[array_rand($firstNames)];
            $ln = $lastNames[array_rand($lastNames)];
            $email = strtolower($fn . '.' . $ln . $i . '@student.epase.edu');

            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'student_id' => 'MAR' . str_pad(200 + $i, 12, '0', STR_PAD_LEFT),
                    'password' => $password,
                    'first_name' => $fn,
                    'middle_name' => chr(65 + rand(0, 25)) . '.',
                    'last_name' => $ln,
                    'ext_name' => '',
                    'role' => 'student',
                    'department_id' => 1,
                    'section' => $sections[array_rand($sections)],
                    'school_year' => '2025-2026',
                    'stat' => 1,
                    'email_verified_at' => now(),
                    'total_points' => rand(0, 500),
                    'current_streak' => rand(0, 14),
                    'last_activity_date' => now()->subDays(rand(0, 7))->toDateString(),
                    'created_at' => now()->subDays(rand(1, 120)),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Created 25 instructors and 50 students.');
    }
}
