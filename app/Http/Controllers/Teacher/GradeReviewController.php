<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeReviewController extends Controller
{
    public function index(Request $request)
    {
        // Validate URL parameters
        $validated = $request->validate([
            'assignment' => ['required', 'integer'],
            'school_year' => ['required', 'string', 'max:20'],
            'term' => ['nullable', 'integer', 'in:1,2,3'],
        ]);

        $schoolYear = $validated['school_year'];
        $selectedTerm = $validated['term'] ?? 1;

        // Get the logged-in teacher's Faculty record
        $faculty = Faculty::where('user_id', Auth::id())
            ->where('status', 'active')
            ->firstOrFail();

        // Make sure the assignment belongs to this teacher
        $assignment = $faculty->teachingAssignments()
            ->whereKey($validated['assignment'])
            ->firstOrFail();

        // Get all active students under this grade level
        $students = Student::where(
            'grade_level',
            $assignment->grade_level
        )
            ->where('school_year', $schoolYear)
            ->where('status', 'active')
            ->with([
                'gradeRecords' => function ($query) use ($assignment, $schoolYear) {
                    $query->where(
                        'teaching_assignment_id',
                        $assignment->id
                    )
                        ->where('school_year', $schoolYear)
                        ->whereIn('term', [1, 2, 3])
                        ->with('assessmentScores');
                }
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('teacher.grade-review', [
            'assignment' => $assignment,
            'students' => $students,
            'schoolYear' => $schoolYear,
            'selectedTerm' => $selectedTerm,
            'terms' => [1, 2, 3],
        ]);
    }
}
