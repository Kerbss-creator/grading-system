<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\PersonRecordService;
use App\Services\SchoolCsvSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function __construct(private PersonRecordService $records, private SchoolCsvSnapshotService $snapshot) {}

    public function index()
    {
        $students = Student::orderBy('created_at', 'desc')->get();
        $availableGrades = $students->pluck('grade_level')->filter()
            ->merge(['Pre-Kindergarten', 'Kindergarten'])
            ->merge(array_map(fn ($n) => 'Grade ' . $n, range(1, 10)))->unique()
            ->sortBy(fn ($level) => preg_match('/(\d+)$/', $level, $m) ? (int) $m[1] + 2 : (strtolower($level) === 'pre-kindergarten' ? 0 : (strtolower($level) === 'kindergarten' ? 1 : 999)))
            ->values();
        $availableYears = $students->pluck('school_year')->filter()->merge(['2026-2027', '2027-2028'])->unique()->sort()->values();
        return view('admin.student-management', compact('students', 'availableGrades', 'availableYears'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $this->records->save('students', $data);
        $this->snapshot->refresh('students');
        return response()->json(['success' => true, 'message' => 'Student added successfully.']);
    }

    public function update(Request $request, int $id)
    {
        $record = Student::findOrFail($id);
        $data = $request->validate($this->rules());
        $this->records->save('students', $data, $record);
        $this->snapshot->refresh('students');
        return response()->json(['success' => true, 'message' => 'Student updated successfully.']);
    }

    public function destroy(int $id)
    {
        $this->records->delete('students', [$id]);
        $this->snapshot->refresh('students');
        return response()->json(['success' => true, 'message' => 'Student moved to Recycle Bin.']);
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array|min:1|max:500', 'ids.*' => 'required|integer|distinct|min:1']);
        $count = $this->records->delete('students', array_map('intval', $data['ids']), 'bulk_delete');
        $this->snapshot->refresh('students');
        return response()->json(['success' => true, 'message' => "$count students moved to Recycle Bin."]);
    }

    public function bulkStatus(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1|max:500',
            'ids.*' => 'required|integer|distinct|min:1',
            'status' => 'required|in:active,inactive',
        ]);
        $count = $this->records->setStatuses('students', array_map('intval', $data['ids']), $data['status']);
        $this->snapshot->refresh('students');
        return response()->json(['success' => true, 'message' => "$count students updated to " . $data['status'] . ".", 'changed' => $count]);
    }

    public function toggleStatus(int $id)
    {
        $status = $this->records->changeStatus('students', $id);
        $this->snapshot->refresh('students');
        return response()->json(['success' => true, 'status' => $status, 'message' => 'Student status updated.']);
    }

    private function rules(): array
    {
        return PersonRecordService::rules('students');
    }
}
