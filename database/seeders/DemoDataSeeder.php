<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('Demo records may be seeded only in local or testing environments.');
        }

        $students = $this->readRows('students', 3);

        $teachers = $this->readRows('teachers', 3);

        $admins = $this->readRows('admins', 2);

        $sharedPassword = Str::random(24);
        $inserted = ['students' => 0, 'teachers' => 0, 'admins' => 0];

        DB::transaction(function () use ($students, $teachers, $admins, $sharedPassword, &$inserted): void {
            foreach ($students as $offset => [$firstName, $lastName, $sex]) {
                $number = 9001 + $offset;
                $studentId = sprintf('STU-%04d', $number);
                $email = sprintf('rene.student%04d@example.test', $number);
                $existingRecord = Student::where('student_id', $studentId)->first();
                $existingUser = User::where('email', $email)->first();
                $fullName = "$firstName $lastName";

                if ($existingRecord || $existingUser) {
                    $this->assertExistingPair($existingRecord, $existingUser, 'student', $email, $fullName);
                    continue;
                }

                $gradeLevel = (string) (7 + ($offset % 4));
                $section = ['A', 'B', 'C', 'D'][intdiv($offset, 4) % 4];

                $user = User::create([
                    'name' => $fullName,
                    'email' => $email,
                    'password' => Hash::make($sharedPassword),
                    'role' => 'student',
                ]);
                Student::create([
                    'user_id' => $user->id,
                    'student_id' => $studentId,
                    'first_name' => $firstName,
                    'middle_name' => null,
                    'last_name' => $lastName,
                    'sex' => $sex,
                    'grade_level' => $gradeLevel,
                    'section' => $section,
                    'school_year' => '2026-2027',
                    'email' => $email,
                    'status' => $offset % 13 === 12 ? 'inactive' : 'active',
                    'is_demo' => true,
                ]);
                $inserted['students']++;
            }

            foreach ($teachers as $offset => [$firstName, $lastName, $subject]) {
                $number = 9001 + $offset;
                $teacherId = sprintf('TCH-%04d', $number);
                $email = sprintf('rene.teacher%04d@example.test', $number);
                $existingRecord = Teacher::where('teacher_id', $teacherId)->first();
                $existingUser = User::where('email', $email)->first();
                $fullName = "$firstName $lastName";

                if ($existingRecord || $existingUser) {
                    $this->assertExistingPair($existingRecord, $existingUser, 'teacher', $email, $fullName);
                    continue;
                }

                $user = User::create([
                    'name' => $fullName,
                    'email' => $email,
                    'password' => Hash::make($sharedPassword),
                    'role' => 'teacher',
                ]);
                Teacher::create([
                    'user_id' => $user->id,
                    'teacher_id' => $teacherId,
                    'first_name' => $firstName,
                    'middle_name' => null,
                    'last_name' => $lastName,
                    'email' => $email,
                    'subject' => $subject,
                    'school_year' => '2026-2027',
                    'status' => $offset % 9 === 8 ? 'inactive' : 'active',
                    'is_demo' => true,
                ]);
                $inserted['teachers']++;
            }

            foreach ($admins as $offset => [$firstName, $lastName]) {
                $number = 9001 + $offset;
                $email = sprintf('rene.admin%04d@example.test', $number);
                $fullName = "$firstName $lastName";
                $existingUser = User::where('email', $email)->first();

                if ($existingUser) {
                    if ($existingUser->name !== $fullName || $existingUser->role !== 'admin') {
                        throw new RuntimeException("Existing email conflicts with demo administrator: {$email}");
                    }
                    continue;
                }

                $user = User::create([
                    'name' => $fullName,
                    'email' => $email,
                    'password' => Hash::make($sharedPassword),
                    'role' => 'admin',
                ]);
                $inserted['admins']++;
            }
        });

        $this->command?->info(sprintf(
            'Demo records seeded: %d students, %d teachers, %d admins (existing matching records skipped).',
            $inserted['students'],
            $inserted['teachers'],
            $inserted['admins']
        ));

        if (array_sum($inserted) > 0) {
            $this->command?->warn('LOCAL DEMO ACCOUNTS ONLY. Save this password now; it will not be shown again:');
            $this->command?->line($sharedPassword);
            $this->command?->info('Example demo admin login: rene.admin9001@example.test');
            $this->command?->warn('On reruns, existing accounts keep their original password; the newly shown password only applies to newly inserted accounts.');
        }
    }

    private function assertExistingPair($record, $user, string $role, string $email, string $fullName): void
    {
        if (! $record || ! $user || $record->email !== $email || $user->role !== $role || $user->name !== $fullName ||
            ($record->first_name . ' ' . $record->last_name) !== $fullName) {
            throw new RuntimeException("Existing {$role} ID/email conflicts with demo record: {$email}. No records were changed.");
        }
    }

    private function readRows(string $type, int $columnCount): array
    {
        $file = database_path('seeders/data/' . $type . '.csv');
        $handle = fopen($file, 'rb');
        if (!$handle) throw new RuntimeException('Demo CSV cannot be opened: ' . $type);
        try {
            $header = fgetcsv($handle);
            if (count($header ?: []) !== $columnCount) throw new RuntimeException('Invalid demo CSV headers: ' . $type);
            $rows = [];
            while (($values = fgetcsv($handle)) !== false) {
                if (count($values) !== $columnCount) throw new RuntimeException('Invalid demo CSV data: ' . $type);
                $rows[] = $values;
            }
            return $rows;
        } finally {
            fclose($handle);
        }
    }
}
