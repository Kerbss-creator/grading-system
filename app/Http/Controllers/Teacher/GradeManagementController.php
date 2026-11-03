<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Student;
use Illuminate\Http\Request;

class GradeManagementController extends Controller
{
    public function index(Request $request)
    {

        
        $user = $request->user();

        // Get the active faculty record of the logged-in teacher
        $faculty = Faculty::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        abort_unless($faculty !== null, 404);

        // Get only the assignment belonging to this teacher
        $assignment = $faculty->teachingAssignments()
            ->where('id', $request->query('assignment'))
            ->first();

        abort_unless($assignment, 404);

        // Selected school year
        $schoolYear = $request->query('school_year', '2026-2027');

        // Subject information
        $subjectName = $assignment->subject;
        $gradeLevel = $assignment->grade_level;

        $subjectCodes = [
            'Mathematics' => 'MATH',
            'Science' => 'SCI',
            'English' => 'ENG',
            'Araling Panlipunan' => 'AP',
            'TLE' => 'TLE',
            'MAPEH' => 'MAPEH',
            'Filipino' => 'FIL',
            'Values Education' => 'VE',
            'Computer Education' => 'COMP',
        ];

        $prefix = $subjectCodes[$subjectName]
            ?? strtoupper(substr($subjectName, 0, 3));

        $gradeNumber = preg_replace('/\D/', '', $gradeLevel);
        $subjectCode = $prefix . '-' . $gradeNumber;

        // Get actual active students for the assigned grade level
        $students = Student::where('grade_level', $gradeLevel)
            ->where('school_year', $schoolYear)
            ->where('status', 'active')
            ->orderBy('last_name')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => trim(
                        $student->first_name . ' ' .
                        ($student->middle_name ? $student->middle_name . ' ' : '') .
                        $student->last_name
                    ),
                ];
            });

        return view('teacher.grade-management', [
            'assignment' => $assignment,
            'subjectName' => $subjectName,
            'subjectCode' => $subjectCode,
            'gradeLevel' => $gradeLevel,
            'schoolYear' => $schoolYear,
            'students' => $students,
        ]);
    }
}
