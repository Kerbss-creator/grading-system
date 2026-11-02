<?php

namespace App\Http\Controllers;

use App\Models\GradeRecord;
use App\Models\ClassAdviser;
use App\Models\SubjectOffering;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Teacher::where('user_id', $request->user()->id)->first();
        $offerings = $teacher ? SubjectOffering::with('subject')->where('teacher_id', $teacher->id)->get() : collect();
        $rosters = [];
        foreach ($offerings as $offering) {
            $rosters[$offering->id] = $offering->studentsQuery()->orderBy('last_name')->get();
        }
        $counts = $teacher ? GradeRecord::whereIn('subject_offering_id', $offerings->pluck('id'))
            ->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total','status') : collect();
        $adviserClasses = $teacher ? ClassAdviser::where('teacher_id',$teacher->id)->get() : collect();
        return view('teacher.teacher-dashboard',compact('teacher','offerings','rosters','counts','adviserClasses'));
    }
}
