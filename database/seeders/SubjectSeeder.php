<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\GradeLevelSubject;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{



    public function run(): void
    {
        $subjects = [
            ['subject_name' => 'Mathematics', 'subject_code' => 'MATH'],
            ['subject_name' => 'Science', 'subject_code' => 'SCI'],
            ['subject_name' => 'English', 'subject_code' => 'ENG'],
            ['subject_name' => 'Filipino', 'subject_code' => 'FIL'],
            ['subject_name' => 'Araling Panlipunan', 'subject_code' => 'AP'],
            ['subject_name' => 'TLE', 'subject_code' => 'TLE'],
            ['subject_name' => 'MAPEH', 'subject_code' => 'MAPEH'],
            ['subject_name' => 'Values Education', 'subject_code' => 'VE'],
            ['subject_name' => 'Computer', 'subject_code' => 'COMP'],
        ];

        DB::transaction(function () use ($subjects) {
            foreach ($subjects as $data) {
                $subject = Subject::updateOrCreate(
                    ['subject_code' => $data['subject_code']],
                    [
                        'subject_name' => $data['subject_name'],
                        'subject_type' => 'Core Subject',
                        'status' => 'active',
                    ]
                );

                foreach ([7, 8, 9, 10] as $grade) {
                    GradeLevelSubject::firstOrCreate([
                        'subject_id' => $subject->id,
                        'grade_level' => $grade,
                    ]);
                }
            }
        });
    }
}