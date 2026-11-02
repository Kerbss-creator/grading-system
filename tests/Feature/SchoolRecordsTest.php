<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SchoolRecordsTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        return User::create([
            'name' => 'Test Administrator', 'email' => 'admin@example.test',
            'password' => 'local-test-only-password', 'role' => 'admin',
        ]);
    }

    private function studentData(): array
    {
        return [
            'student_id' => 'STU-T100', 'first_name' => 'Noli', 'middle_name' => null,
            'last_name' => 'Tangrene', 'grade_level' => 'Grade 7', 'section' => 'A',
            'sex' => 'Male', 'school_year' => '2026-2027',
            'email' => 'noli@example.test', 'status' => 'active',
        ];
    }

    public function test_non_admin_cannot_access_record_management(): void
    {
        $student = User::create([
            'name' => 'Student', 'email' => 'student@example.test',
            'password' => 'local-test-only-password', 'role' => 'student',
        ]);
        $this->actingAs($student)->get('/admin/students')->assertForbidden();
        $this->actingAs($student)->get('/admin/users')->assertForbidden();
        $this->actingAs($student)->get('/admin/activity-log')->assertForbidden();
        $this->actingAs($student)->get('/admin/imports/students')->assertForbidden();
    }

    public function test_create_bulk_delete_restore_are_logged_and_accounts_follow_records(): void
    {
        $this->actingAs($this->administrator());
        $this->postJson('/admin/students', $this->studentData())->assertOk();
        $student = Student::firstOrFail();
        $this->assertNotNull($student->user_id);
        $this->assertDatabaseHas('activity_logs', ['action' => 'create', 'record_key' => 'STU-T100']);

        $this->deleteJson('/admin/students/bulk', ['ids' => [$student->id]])->assertOk();
        $this->assertSoftDeleted('students', ['id' => $student->id]);
        $this->assertSoftDeleted('users', ['id' => $student->user_id]);
        $this->post("/admin/recycle-bin/students/{$student->id}/restore")->assertRedirect();
        $this->assertDatabaseHas('students', ['id' => $student->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('users', ['id' => $student->user_id, 'deleted_at' => null]);
        $this->assertEquals(['create', 'delete', 'restore'], ActivityLog::orderBy('id')->pluck('action')->all());
    }

    public function test_csv_preview_does_not_write_and_confirm_imports_selected_rows(): void
    {
        $this->actingAs($this->administrator());
        Storage::fake('local');
        $headers = implode(',', \App\Services\PersonRecordService::columns('students'));
        $csv = $headers . "\nSTU-T100,Noli,,Tangrene,Grade 7,A,Male,2026-2027,noli@example.test,active\n";
        $file = UploadedFile::fake()->createWithContent('students.csv', $csv);
        $response = $this->post('/admin/imports/students/preview', ['file' => $file]);
        $response->assertOk();
        $this->assertDatabaseCount('students', 0);
        $token = session('school_import.token');
        $this->post('/admin/imports/students/confirm', [
            'token' => $token, 'mode' => 'add_only', 'lines' => [2],
        ])->assertRedirect('/admin/imports/students');
        $this->assertDatabaseCount('students', 1);
        $this->assertDatabaseHas('activity_logs', ['action' => 'csv_import', 'record_type' => 'students']);
    }
}
