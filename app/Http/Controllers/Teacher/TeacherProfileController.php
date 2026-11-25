<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeacherProfileController extends Controller
{
    /**
     * Display the authenticated teacher's profile.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $faculty = $user->faculty()
            ->with([
                'teachingAssignments.gradeRecords.student',
            ])
            ->first();

        $teachingAssignments = $faculty
            ? $faculty->teachingAssignments
                ->where('school_year', '2026-2027')
            : collect();

        $assignedClasses = $teachingAssignments
            ->pluck('grade_level')
            ->unique()
            ->sort()
            ->values();

        $subjects = $teachingAssignments
            ->pluck('subject')
            ->unique()
            ->values();

        $studentCount = $teachingAssignments
            ->flatMap(function ($assignment) {
                return $assignment->gradeRecords
                    ->pluck('student_id');
            })
            ->unique()
            ->count();

        return view('teacher.profile', compact(
            'user',
            'faculty',
            'teachingAssignments',
            'assignedClasses',
            'subjects',
            'studentCount'
        ));
    }

    /**
     * Upload or replace the teacher's profile photo.
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $teacher = $request->user();

        // Store the new photo first.
        $newPath = $request->file('profile_photo')
            ->store('profile-photos', 'public');

        if (!$newPath) {
            return back()->withErrors([
                'profile_photo' => 'Unable to upload the selected photo.',
            ]);
        }

        // Remember the previous photo.
        $oldPath = $teacher->profile_photo_path;

        // Update the database with the new photo path.
        $teacher->profile_photo_path = $newPath;
        $teacher->save();

        // Delete the previous photo only after successful saving.
        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route('teacher.profile')
            ->with('success', 'Profile photo updated successfully.');
    }

    /**
     * Remove the teacher's profile photo.
     */
    public function removePhoto(Request $request)
    {
        $teacher = $request->user();

        if ($teacher->profile_photo_path) {
            Storage::disk('public')->delete(
                $teacher->profile_photo_path
            );

            $teacher->profile_photo_path = null;
            $teacher->save();
        }

        return redirect()
            ->route('teacher.profile')
            ->with('success', 'Profile photo removed successfully.');
    }
}
