<?php

namespace App\Http\Controllers;

use App\Models\GradeRecord;
use App\Models\ClassAdviser;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectOffering;
use App\Models\Teacher;
use App\Services\ActivityRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AcademicController extends Controller
{
    public function __construct(private ActivityRecorder $audit) {}

    public function subjects(Request $request)
    {
        $subjects = Subject::withCount('offerings')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . trim($request->search) . '%'))
            ->when($request->filled('grade_level'), fn ($q) => $q->where('grade_level', $request->grade_level))
            ->when($request->filled('status') && in_array($request->status, ['active','inactive'], true),
                fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->orderBy('school_year', 'desc')->orderBy('grade_level')->orderBy('name')->paginate(25)->withQueryString();
        $gradeLevels = Student::distinct()->orderBy('grade_level')->pluck('grade_level');
        return view('admin.subject-management', compact('subjects', 'gradeLevels'));
    }

    public function storeSubject(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'grade_level' => ['required','string','max:60'],
            'school_year' => ['required','regex:/^\d{4}-\d{4}$/'],
        ]);
        $data['name'] = trim($data['name']);
        if (!Student::where('grade_level', $data['grade_level'])->where('school_year', $data['school_year'])->exists()) {
            throw ValidationException::withMessages(['grade_level' => 'There are no enrolled student records for this grade and school year. Verify the academic year before adding a subject.']);
        }
        if (str_contains(strtolower($data['grade_level']), 'unverified')) {
            throw ValidationException::withMessages(['grade_level' => 'Verify the official grade level before assigning a subject.']);
        }
        $subject = Subject::firstOrCreate($data, ['is_active' => true]);
        if ($subject->wasRecentlyCreated) {
            $this->audit->record('create', 'subjects', (string) $subject->id, null, $subject->only(['name','grade_level','school_year']));
        }
        return redirect()->route('admin.subjects.show', $subject)->with('success', 'Subject saved. Assign a section and teacher before recording grades.');
    }

    public function subject(Subject $subject)
    {
        $subject->load(['offerings.teacher']);
        $teachers = Teacher::where('school_year', $subject->school_year)->orderBy('last_name')->get();
        $enrolments = [];
        foreach ($subject->offerings as $offering) {
            $enrolments[$offering->id] = $offering->studentsQuery()->orderBy('last_name')->get();
        }
        return view('admin.subject-detail', compact('subject', 'teachers', 'enrolments'));
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'name' => ['required','string','max:150',
                Rule::unique('subjects','name')->where('grade_level',$subject->grade_level)
                    ->where('school_year',$subject->school_year)->ignore($subject->id)],
            'is_active' => ['required','boolean'],
        ]);
        $before = $subject->only(['name','is_active']);
        $subject->update($data);
        $this->audit->record('update','subjects',(string)$subject->id,$before,$subject->only(['name','is_active']));
        return back()->with('success','Subject information updated. Existing saved grade records are preserved.');
    }

    public function storeOffering(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'section' => ['required','string','max:60'],
            'teacher_id' => ['nullable','integer', Rule::exists('teachers','id')->where('school_year', $subject->school_year)],
            'meeting_days' => ['nullable','string','max:90'],
            'starts_at' => ['nullable','date_format:H:i','required_with:ends_at'],
            'ends_at' => ['nullable','date_format:H:i','required_with:starts_at','after:starts_at'],
            'room' => ['nullable','string','max:90'],
        ]);
        if (!$subject->is_active) throw ValidationException::withMessages(['section' => 'Inactive subjects cannot receive a new class assignment.']);
        $data['section'] = trim($data['section']);
        if ($data['section'] !== 'ALL' && !Student::where('grade_level',$subject->grade_level)
            ->where('school_year',$subject->school_year)->where('section',$data['section'])->exists()) {
            throw ValidationException::withMessages(['section' => 'No students match this section, grade, and school year. Use ALL only when intentionally assigning the entire grade.']);
        }
        if (SubjectOffering::where('subject_id', $subject->id)->where('section',$data['section'])->exists()) {
            throw ValidationException::withMessages(['section' => 'This class already has an offering for the subject.']);
        }
        $this->checkScheduleConflict($subject, $data);
        $offering = $subject->offerings()->create($data);
        $this->audit->record('create','subject_offerings',(string)$offering->id,null,$offering->only(['subject_id','teacher_id','section','meeting_days','starts_at','ends_at','room']));
        return back()->with('success','Class and schedule assigned.');
    }

    public function updateOffering(Request $request, SubjectOffering $offering)
    {
        $offering->load('subject');
        $data = $request->validate([
            'teacher_id' => ['nullable','integer',Rule::exists('teachers','id')->where('school_year',$offering->subject->school_year)],
            'meeting_days' => ['nullable','string','max:90'],
            'starts_at' => ['nullable','date_format:H:i','required_with:ends_at'],
            'ends_at' => ['nullable','date_format:H:i','required_with:starts_at','after:starts_at'],
            'room' => ['nullable','string','max:90'],
        ]);
        if ($offering->grades()->exists() && (int)($data['teacher_id'] ?? 0) !== (int)$offering->teacher_id) {
            throw ValidationException::withMessages(['teacher_id' => 'Teacher cannot be reassigned after grades have been recorded. Contact an administrator to plan a controlled reassignment.']);
        }
        $this->checkScheduleConflict($offering->subject, $data + ['section'=>$offering->section], $offering->id);
        $before = $offering->only(['teacher_id','meeting_days','starts_at','ends_at','room']);
        $offering->update($data);
        $this->audit->record('update','subject_offerings',(string)$offering->id,$before,$offering->only(['teacher_id','meeting_days','starts_at','ends_at','room']));
        return back()->with('success','Class assignment and schedule updated.');
    }

    private function checkScheduleConflict(Subject $subject, array $data, ?int $exceptId = null): void
    {
        // Timetable remains optional; validate overlaps only when days and times are supplied.
        if (empty($data['meeting_days']) || empty($data['starts_at']) || empty($data['ends_at'])) return;
        $days = collect(explode(',', $data['meeting_days']))->map(fn($d) => strtolower(trim($d)))->filter()->unique();
        $allowed = ['mon','tue','wed','thu','fri','sat','sun'];
        if ($days->isEmpty() || $days->diff($allowed)->isNotEmpty()) {
            throw ValidationException::withMessages(['meeting_days' => 'Use comma-separated day abbreviations: Mon,Tue,Wed,Thu,Fri,Sat,Sun.']);
        }
        $otherOfferings = SubjectOffering::with('subject')->whereHas('subject',
                fn ($q) => $q->where('school_year',$subject->school_year))
            ->when($exceptId,fn($q) => $q->where('id','!=',$exceptId))
            ->whereNotNull('starts_at')->whereNotNull('ends_at')->whereNotNull('meeting_days')->get();
        foreach ($otherOfferings as $other) {
            $existingDays = collect(explode(',', $other->meeting_days))->map(fn($d)=>strtolower(trim($d)));
            if ($days->intersect($existingDays)->isEmpty()) continue;
            if (!(($data['starts_at'] < substr($other->ends_at,0,5)) && ($data['ends_at'] > substr($other->starts_at,0,5)))) continue;
            $sameTeacher = !empty($data['teacher_id']) && (int)$data['teacher_id'] === (int)$other->teacher_id;
            $sameClass = $subject->grade_level === $other->subject->grade_level &&
                ($data['section'] === $other->section || $data['section'] === 'ALL' || $other->section === 'ALL');
            if ($sameTeacher || $sameClass) {
                throw ValidationException::withMessages(['starts_at' => 'Schedule overlaps another assignment for the same teacher or grade/section.']);
            }
        }
    }

    public function gradeLevels(Request $request)
    {
        $groups = Student::query()->selectRaw('grade_level, school_year, section, COUNT(*) as students_count')
            ->when($request->filled('school_year'), fn($q) => $q->where('school_year',$request->school_year))
            ->groupBy('grade_level','school_year','section')->orderByDesc('school_year')->orderBy('grade_level')->orderBy('section')->get();
        $advisers = ClassAdviser::with('teacher')->get()->keyBy(fn($a) => $a->grade_level.'|'.$a->section.'|'.$a->school_year);
        $teachers = Teacher::orderBy('last_name')->get();
        return view('admin.grade-level', compact('groups','advisers','teachers'));
    }

    public function saveAdviser(Request $request)
    {
        $data = $request->validate([
            'grade_level'=>['required','string','max:60'],
            'section'=>['required','string','max:60'],
            'school_year'=>['required','regex:/^\d{4}-\d{4}$/'],
            'teacher_id'=>['nullable','integer','exists:teachers,id'],
        ]);
        if (!Student::where('grade_level',$data['grade_level'])->where('section',$data['section'])
            ->where('school_year',$data['school_year'])->exists()) {
            throw ValidationException::withMessages(['section'=>'No students match that class and school year.']);
        }
        if ($data['teacher_id'] && !Teacher::where('id',$data['teacher_id'])->where('school_year',$data['school_year'])->exists()) {
            throw ValidationException::withMessages(['teacher_id'=>'Teacher school year does not match the selected class.']);
        }
        DB::transaction(function () use ($data) {
            $identity = collect($data)->only(['grade_level','section','school_year'])->all();
            $existing = ClassAdviser::where($identity)->lockForUpdate()->first();
            $before = $existing?->only(['teacher_id']);
            if ($data['teacher_id']) {
                ClassAdviser::updateOrCreate($identity,['teacher_id'=>$data['teacher_id']]);
            } elseif ($existing) {
                $existing->delete();
            }
            $this->audit->record('assign','class_advisers',implode('|',$identity),$before,['teacher_id'=>$data['teacher_id']]);
        });
        return back()->with('success','Class adviser assignment updated.');
    }

    public function student(Student $student)
    {
        $offerings = SubjectOffering::with(['subject','teacher'])
            ->whereHas('subject', fn ($q) => $q->where('grade_level',$student->grade_level)
                ->where('school_year',$student->school_year)->where('is_active',true))
            ->where(fn ($q) => $q->where('section','ALL')->orWhere('section',$student->section ?: '__none__'))
            ->get();
        $grades = GradeRecord::with(['offering.subject','offering.teacher','reviewer'])
            ->where('student_id',$student->id)->orderBy('quarter')->get();
        $adviser = ClassAdviser::with('teacher')->where('grade_level',$student->grade_level)
            ->where('section',$student->section)->where('school_year',$student->school_year)->first();
        return view('admin.student-detail',compact('student','offerings','grades','adviser'));
    }

    public function teacher(Teacher $teacher)
    {
        $offerings = SubjectOffering::with('subject')->where('teacher_id',$teacher->id)->get();
        $rosters = [];
        foreach ($offerings as $offering) $rosters[$offering->id] = $offering->studentsQuery()->orderBy('last_name')->get();
        $adviserClasses = ClassAdviser::where('teacher_id',$teacher->id)->get();
        return view('admin.teacher-detail',compact('teacher','offerings','rosters','adviserClasses'));
    }

    public function grades(Request $request)
    {
        $isTeacher = $request->user()->role === 'teacher';
        $teacher = $isTeacher ? Teacher::where('user_id',$request->user()->id)->first() : null;
        $query = SubjectOffering::with(['subject','teacher'])->whereHas('subject', fn($q) => $q->where('is_active',true));
        if ($isTeacher) $query->where('teacher_id', $teacher?->id ?? 0);
        $offerings = $query->orderBy('subject_id')->get();
        $selected = $offerings->firstWhere('id', (int) $request->query('offering_id'));
        $selected ??= $offerings->first();
        $quarter = (int) $request->query('quarter',1);
        if (!in_array($quarter,[1,2,3,4],true)) $quarter = 1;
        $students = $selected?->studentsQuery()->orderBy('last_name')->get() ?? collect();
        $grades = $selected ? GradeRecord::where('subject_offering_id',$selected->id)->where('quarter',$quarter)->get()->keyBy('student_id') : collect();
        $layout = $isTeacher ? 'layouts.teacher' : 'layouts.admin';
        return view('admin.grade-management', compact('offerings','selected','quarter','students','grades','layout','isTeacher'));
    }

    private function allowedOffering(Request $request, SubjectOffering $offering): void
    {
        abort_unless($request->user()->role === 'admin' ||
            ($request->user()->role === 'teacher' && Teacher::where('user_id', $request->user()->id)
                ->where('id', $offering->teacher_id)->exists()), 403);
        abort_unless($offering->subject->is_active,403);
    }

    public function saveGrade(Request $request, SubjectOffering $offering)
    {
        $this->allowedOffering($request,$offering);
        $data = $request->validate([
            'student_id' => ['required','integer','exists:students,id'],
            'quarter' => ['required','integer','between:1,4'],
            'grade' => ['required','numeric','between:0,100'],
        ]);
        if (!$offering->studentsQuery()->whereKey($data['student_id'])->exists()) {
            throw ValidationException::withMessages(['student_id' => 'This student is not assigned to the selected subject/class/year.']);
        }
        DB::transaction(function () use ($offering,$data) {
            $record = GradeRecord::where('subject_offering_id',$offering->id)->where('student_id',$data['student_id'])
                ->where('quarter',$data['quarter'])->lockForUpdate()->first();
            if ($record && !in_array($record->status,['draft','returned'],true)) {
                throw ValidationException::withMessages(['grade' => 'Pending or approved grades cannot be edited. Return them for correction first.']);
            }
            $before = $record?->only(['grade','status']);
            $record ??= new GradeRecord(['subject_offering_id'=>$offering->id,'student_id'=>$data['student_id'],'quarter'=>$data['quarter']]);
            $record->fill(['grade'=>$data['grade'],'status'=>'draft','review_note'=>null,'reviewed_by'=>null,'reviewed_at'=>null]);
            $record->save();
            $this->audit->record($before ? 'update' : 'create','grade_records',(string)$record->id,$before,$record->only(['grade','status']),['offering_id'=>$offering->id,'quarter'=>$data['quarter']]);
        });
        return back()->with('success','Draft grade saved. Submit it when ready for approval.');
    }

    public function submitGrades(Request $request)
    {
        $data = $request->validate(['ids' => ['required','array','min:1','max:200'], 'ids.*'=>['required','integer','distinct','exists:grade_records,id']]);
        DB::transaction(function () use ($request,$data) {
            $records = GradeRecord::with('offering.subject')->whereIn('id',$data['ids'])->lockForUpdate()->get();
            foreach ($records as $record) {
                $this->allowedOffering($request,$record->offering);
                if (!in_array($record->status,['draft','returned'],true) || $record->grade === null) {
                    throw ValidationException::withMessages(['ids'=>'Only completed draft or returned grades can be submitted.']);
                }
            }
            foreach ($records as $record) {
                $before = $record->only(['grade','status']);
                $record->update(['status'=>'pending','submitted_by'=>$request->user()->id,'submitted_at'=>now(),
                    'review_note'=>null,'reviewed_by'=>null,'reviewed_at'=>null]);
                $this->audit->record('submit','grade_records',(string)$record->id,$before,$record->only(['grade','status']));
            }
        });
        return back()->with('success','Selected grades submitted for administrator approval.');
    }

    private function applyReviewerScope($query, Request $request)
    {
        if ($request->user()->role === 'admin') return $query;
        abort_unless($request->user()->role === 'teacher', 403);
        $teacher = Teacher::where('user_id',$request->user()->id)->first();
        $classes = $teacher ? ClassAdviser::where('teacher_id',$teacher->id)->get() : collect();
        // An adviser can review another person's submission for students in an
        // explicitly assigned advisory class, never their own submitted grades.
        return $query->where('submitted_by', '!=', $request->user()->id)
            ->whereHas('offering', fn($q) => $q->where('teacher_id','!=',$teacher?->id ?? 0))
            ->whereHas('student', function ($q) use ($classes) {
                $q->where(function ($nested) use ($classes) {
                    if ($classes->isEmpty()) {
                        $nested->whereRaw('1 = 0');
                        return;
                    }
                    foreach ($classes as $class) {
                        $nested->orWhere(function ($q) use ($class) {
                            $q->where('grade_level',$class->grade_level)
                                ->where('section',$class->section)->where('school_year',$class->school_year);
                        });
                    }
                });
            });
    }

    public function approvals(Request $request)
    {
        $status = $request->query('status','pending');
        if (!in_array($status,['all','pending','approved','returned','draft'],true)) $status = 'pending';
        $base = $this->applyReviewerScope(GradeRecord::query(),$request);
        $counts = (clone $base)->selectRaw('status, COUNT(*) total')->groupBy('status')->pluck('total','status');
        $query = $base->with(['student','offering.subject','offering.teacher','submitter','reviewer'])
            ->when($status !== 'all',fn($q) => $q->where('status',$status))
            ->when($request->filled('grade_level'), fn($q) => $q->whereHas('student',fn($s) => $s->where('grade_level',$request->grade_level)))
            ->when($request->filled('subject_id'), fn($q) => $q->whereHas('offering',fn($o) => $o->where('subject_id',$request->subject_id)))
            ->when($request->filled('student_id'),fn($q) => $q->where('student_id',$request->student_id));
        $records = $query->orderByDesc('submitted_at')->orderBy('id')->paginate(40)->withQueryString();
        $subjects = Subject::orderBy('name')->get();
        $isTeacher = $request->user()->role === 'teacher';
        $layout = $isTeacher ? 'layouts.teacher' : 'layouts.admin';
        return view('admin.grade-approval',compact('records','status','counts','subjects','isTeacher','layout'));
    }

    public function review(Request $request)
    {
        $data = $request->validate([
            'ids'=>['required','array','min:1','max:200'], 'ids.*'=>['required','integer','distinct','exists:grade_records,id'],
            'decision'=>['required',Rule::in(['approved','returned'])],
            'review_note'=>['required_if:decision,returned','nullable','string','max:1000'],
        ]);
        DB::transaction(function () use ($request,$data) {
            $records = GradeRecord::with(['student','offering.teacher'])->whereIn('id',$data['ids'])->lockForUpdate()->get();
            if ($records->count() !== count($data['ids']) || $records->contains(fn($g) => $g->status !== 'pending')) {
                throw ValidationException::withMessages(['ids' => 'Only pending grade records can be reviewed. Reload and select pending records.']);
            }
            if ($request->user()->role === 'teacher') {
                $teacher = Teacher::where('user_id',$request->user()->id)->first();
                $classes = $teacher ? ClassAdviser::where('teacher_id',$teacher->id)->get() : collect();
                foreach ($records as $record) {
                    $belongsToAdvisory = $classes->contains(fn ($a) =>
                        $a->grade_level === $record->student->grade_level &&
                        $a->section === $record->student->section &&
                        $a->school_year === $record->student->school_year);
                    if (!$belongsToAdvisory || (int)$record->submitted_by === (int)$request->user()->id ||
                        (int)$record->offering->teacher?->user_id === (int)$request->user()->id) {
                        abort(403, 'Teachers may review only another teacher’s grades for their assigned advisory class.');
                    }
                }
            }
            foreach ($records as $record) {
                $before = $record->only(['status','review_note']);
                $record->update(['status'=>$data['decision'],'reviewed_by'=>$request->user()->id,'reviewed_at'=>now(),
                    'review_note'=>$data['decision'] === 'returned' ? $data['review_note'] : null]);
                $this->audit->record($data['decision'] === 'approved' ? 'approve' : 'return', 'grade_records',
                    (string)$record->id,$before,$record->only(['status','review_note']),['student_id'=>$record->student_id]);
            }
        });
        return back()->with('success','Selected grade decisions saved and logged.');
    }
}
