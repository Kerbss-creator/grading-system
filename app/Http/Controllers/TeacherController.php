<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Services\PersonRecordService;
use App\Services\SchoolCsvSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function __construct(private PersonRecordService $records, private SchoolCsvSnapshotService $snapshot) {}

    public function index()
    {
        $teachers = Teacher::orderBy('created_at', 'desc')->get();
        $availableSubjects = $teachers->pluck('subject')->filter()->unique()->sort()->values();
        return view('admin.teacher-management', compact('teachers','availableSubjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $this->records->save('teachers', $data);
        $this->snapshot->refresh('teachers');
        return response()->json(['success' => true, 'message' => 'Teacher added successfully.']);
    }

    public function update(Request $request, int $id)
    {
        $record = Teacher::findOrFail($id);
        $data = $request->validate($this->rules());
        $this->records->save('teachers', $data, $record);
        $this->snapshot->refresh('teachers');
        return response()->json(['success' => true, 'message' => 'Teacher updated successfully.']);
    }

    public function destroy(int $id)
    {
        $this->records->delete('teachers', [$id]);
        $this->snapshot->refresh('teachers');
        return response()->json(['success' => true, 'message' => 'Teacher moved to Recycle Bin.']);
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array|min:1|max:500', 'ids.*' => 'required|integer|distinct|min:1']);
        $count = $this->records->delete('teachers', array_map('intval', $data['ids']), 'bulk_delete');
        $this->snapshot->refresh('teachers');
        return response()->json(['success' => true, 'message' => "$count teachers moved to Recycle Bin."]);
    }

    public function bulkStatus(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1|max:500',
            'ids.*' => 'required|integer|distinct|min:1',
            'status' => 'required|in:active,inactive',
        ]);
        $count = $this->records->setStatuses('teachers', array_map('intval', $data['ids']), $data['status']);
        $this->snapshot->refresh('teachers');
        return response()->json(['success' => true, 'message' => "$count teachers updated to " . $data['status'] . ".", 'changed' => $count]);
    }

    public function toggleStatus(int $id)
    {
        $status = $this->records->changeStatus('teachers', $id);
        $this->snapshot->refresh('teachers');
        return response()->json(['success' => true, 'status' => $status, 'message' => 'Teacher status updated.']);
    }

    private function rules(): array
    {
        return PersonRecordService::rules('teachers');
    }
}
