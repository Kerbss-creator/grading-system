<?php

namespace App\Http\Controllers\Teacher;

use function view;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Student;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Get the active faculty record of the logged-in teacher
        $faculty = Faculty::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        // Get assigned subjects
        $assignments = $faculty
            ? $faculty->teachingAssignments()->get()
            : collect();

        // Attach actual active student counts to each assignment
        $assignments->each(function ($assignment) {
            $assignment->student_count = Student::where(
                'grade_level',
                $assignment->grade_level
            )
                ->where('status', 'active')
                ->count();
        });

    
        return view('teacher.subjects', [
            'assignments' => $assignments,
        ]);
    }
}