<?php

namespace App\Imports;

use App\Constants\Roles;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class UsersImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected array $errors = [];
    protected int $imported = 0;
    protected int $skipped = 0;
    protected array $skippedRows = [];
    protected ?string $defaultPassword;
    protected bool $autoActivate;
    protected ?string $defaultRole;
    protected ?int $defaultDepartmentId;

    public function __construct(
        ?string $defaultPassword = null,
        bool $autoActivate = false,
        ?string $defaultRole = 'student',
        ?int $defaultDepartmentId = null
    ) {
        $this->defaultPassword = $defaultPassword;
        $this->autoActivate = $autoActivate;
        $this->defaultRole = $defaultRole;
        $this->defaultDepartmentId = $defaultDepartmentId;
    }

    public function collection(Collection $rows)
    {
        $departments = Department::pluck('id', 'name')->toArray();
        $departmentsByCode = Department::pluck('id', 'code')->toArray();

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because of header row and 0-index

            // Skip completely empty rows
            if ($this->isEmptyRow($row)) {
                continue;
            }

            // Normalize row data
            $data = $this->normalizeRow($row);

            // Validate required fields
            $validation = $this->validateRow($data, $rowNumber);
            if ($validation !== true) {
                $this->skipped++;
                $this->skippedRows[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => $validation,
                ];
                continue;
            }

            // Check for duplicate email
            if (User::where('email', $data['email'])->exists()) {
                $this->skipped++;
                $this->skippedRows[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Email already exists in the system'],
                ];
                continue;
            }

            // Check for duplicate student_id if provided
            if (!empty($data['student_id']) && User::where('student_id', $data['student_id'])->exists()) {
                $this->skipped++;
                $this->skippedRows[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Student ID already exists in the system'],
                ];
                continue;
            }

            // Resolve department
            $departmentId = $this->resolveDepartment($data, $departments, $departmentsByCode);

            // Create user
            try {
                User::create([
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'] ?? null,
                    'last_name' => $data['last_name'],
                    'ext_name' => $data['ext_name'] ?? null,
                    'email' => $data['email'],
                    'password' => Hash::make($data['password'] ?? $this->defaultPassword ?? 'Password123!'),
                    'role' => $data['role'] ?? $this->defaultRole ?? 'student',
                    'student_id' => $data['student_id'] ?? null,
                    'section' => $data['section'] ?? null,
                    'room_number' => $data['room_number'] ?? null,
                    'department_id' => $departmentId,
                    'stat' => $this->autoActivate ? 1 : 0,
                    'email_verified_at' => $this->autoActivate ? now() : null,
                ]);

                $this->imported++;
            } catch (\Exception $e) {
                $this->skipped++;
                $this->skippedRows[] = [
                    'row' => $rowNumber,
                    'data' => $data,
                    'errors' => ['Database error: ' . $e->getMessage()],
                ];
            }
        }
    }

    protected function isEmptyRow($row): bool
    {
        foreach ($row as $value) {
            if (!empty(trim((string) $value))) {
                return false;
            }
        }
        return true;
    }

    protected function normalizeRow($row): array
    {
        return [
            'first_name' => trim((string) ($row['first_name'] ?? $row['firstname'] ?? $row['first'] ?? '')),
            'middle_name' => trim((string) ($row['middle_name'] ?? $row['middlename'] ?? $row['middle'] ?? '')),
            'last_name' => trim((string) ($row['last_name'] ?? $row['lastname'] ?? $row['last'] ?? $row['surname'] ?? '')),
            'ext_name' => trim((string) ($row['ext_name'] ?? $row['extension'] ?? $row['suffix'] ?? '')),
            'email' => strtolower(trim((string) ($row['email'] ?? $row['email_address'] ?? ''))),
            'password' => trim((string) ($row['password'] ?? '')),
            'role' => strtolower(trim((string) ($row['role'] ?? $row['user_role'] ?? $row['type'] ?? ''))),
            'student_id' => trim((string) ($row['student_id'] ?? $row['studentid'] ?? $row['id_number'] ?? '')),
            'section' => trim((string) ($row['section'] ?? $row['class'] ?? $row['class_section'] ?? '')),
            'room_number' => trim((string) ($row['room_number'] ?? $row['room'] ?? '')),
            'department' => trim((string) ($row['department'] ?? $row['dept'] ?? $row['department_name'] ?? '')),
            'department_id' => trim((string) ($row['department_id'] ?? $row['dept_id'] ?? '')),
        ];
    }

    protected function validateRow(array $data, int $rowNumber): array|bool
    {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors[] = 'First name is required';
        } elseif (!preg_match('/^[\pL\s\-\'\.]+$/u', $data['first_name'])) {
            $errors[] = 'First name must contain only letters, spaces, hyphens, or apostrophes';
        }

        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required';
        } elseif (!preg_match('/^[\pL\s\-\'\.]+$/u', $data['last_name'])) {
            $errors[] = 'Last name must contain only letters, spaces, hyphens, or apostrophes';
        }

        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        // Validate role if provided
        if (!empty($data['role']) && !in_array($data['role'], [Roles::ADMIN, Roles::INSTRUCTOR, Roles::STUDENT])) {
            $errors[] = 'Invalid role (must be admin, instructor, or student)';
        }

        return empty($errors) ? true : $errors;
    }

    protected function resolveDepartment(array $data, array $departments, array $departmentsByCode): ?int
    {
        // Check by ID first
        if (!empty($data['department_id']) && is_numeric($data['department_id'])) {
            return (int) $data['department_id'];
        }

        // Check by name
        if (!empty($data['department'])) {
            $deptName = $data['department'];

            // Exact match
            if (isset($departments[$deptName])) {
                return $departments[$deptName];
            }

            // Case-insensitive match
            foreach ($departments as $name => $id) {
                if (strtolower($name) === strtolower($deptName)) {
                    return $id;
                }
            }

            // Check by code
            if (isset($departmentsByCode[$deptName])) {
                return $departmentsByCode[$deptName];
            }

            foreach ($departmentsByCode as $code => $id) {
                if (strtolower($code) === strtolower($deptName)) {
                    return $id;
                }
            }
        }

        return $this->defaultDepartmentId;
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }

    public function getSkippedRows(): array
    {
        return $this->skippedRows;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
