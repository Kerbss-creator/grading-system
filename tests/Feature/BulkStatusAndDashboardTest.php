<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BulkStatusAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        return User::create([
            'name' => 'Test Administrator', 'email' => 'admin@example.test',
            'role' => 'admin', 'password' => 'Password12345', 'is_active' => true,
        ]);
    }

    public function test_student_bulk_status_changes_both_record_and_login(): void
    {
        Storage::fake('local');
        $admin = $this->administrator();
        $user = User::create(['name' => 'Test Student', 'email' => 'student@example.test',
            'role' => 'student', 'password' => 'Password12345', 'is_active' => true]);
        $student = Student::create(['student_id' => 'ST-01', 'first_name' => 'Test',
            'last_name' => 'Student', 'grade_level' => 'Grade 1', 'sex' => 'Female',
            'school_year' => '2025-2026', 'email' => 'student@example.test',
            'status' => 'active', 'user_id' => $user->id]);
        $this->actingAs($admin)->patchJson('/admin/students/bulk-status', [
            'ids' => [$student->id], 'status' => 'inactive',
        ])->assertOk()->assertJsonPath('changed', 1);
        $this->assertSame('inactive', $student->fresh()->status);
        $this->assertFalse($user->fresh()->is_active);
        $this->assertDatabaseHas('activity_logs', ['action' => 'status_change', 'record_type' => 'students']);
        $this->patchJson('/admin/students/bulk-status', [
            'ids' => [$student->id], 'status' => 'inactive',
        ])->assertOk()->assertJsonPath('changed', 0);
    }

    public function test_teacher_bulk_status_is_atomic_with_bad_selected_id(): void
    {
        Storage::fake('local');
        $admin = $this->administrator();
        $user = User::create(['name' => 'Test Teacher', 'email' => 'teacher@example.test',
            'role' => 'teacher', 'password' => 'Password12345', 'is_active' => true]);
        $teacher = Teacher::create(['teacher_id' => 'TC-01', 'first_name' => 'Test',
            'last_name' => 'Teacher', 'subject' => 'Science', 'school_year' => '2025-2026',
            'email' => 'teacher@example.test', 'status' => 'active', 'user_id' => $user->id]);
        $this->actingAs($admin)->patchJson('/admin/teachers/bulk-status', [
            'ids' => [$teacher->id, 999999], 'status' => 'inactive',
        ])->assertStatus(422);
        $this->assertSame('active', $teacher->fresh()->status);
        $this->patchJson('/admin/teachers/bulk-status', [
            'ids' => [$teacher->id], 'status' => 'inactive',
        ])->assertOk()->assertJsonPath('changed', 1);
        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        Storage::fake('local');
        $admin = $this->administrator();
        $this->actingAs($admin)->patchJson('/admin/users/admins/bulk-status', [
            'ids' => [$admin->id], 'status' => 'inactive',
        ])->assertStatus(422);
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_dashboard_shows_saved_grades_without_duplicate_grade_prefix(): void
    {
        $admin = $this->administrator();
        $user = User::create(['name' => 'Demo Student', 'email' => 'demo@example.test',
            'role' => 'student', 'password' => 'Password12345', 'is_active' => false]);
        Student::create(['student_id' => 'ST-02', 'first_name' => 'Demo',
            'last_name' => 'Student', 'grade_level' => 'Grade 1', 'sex' => 'Male',
            'school_year' => '2025-2026', 'email' => 'demo@example.test',
            'status' => 'inactive', 'user_id' => $user->id]);
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()
            ->assertSee('Grade 1')->assertDontSee('Grade Grade 1')->assertSee('1 inactive');
    }

    public function test_activity_records_created_by_one_admin_are_visible_to_another(): void
    {
        $actor = $this->administrator();
        $viewer = User::create([
            'name' => 'Reviewing Administrator',
            'email' => 'reviewing-admin@example.test',
            'role' => 'admin',
            'password' => 'Password12345',
            'is_active' => true,
        ]);

        $this->actingAs($actor);
        app(\App\Services\ActivityRecorder::class)->record(
            'update', 'students', 'TEST-STUDENT-123', null,
            ['first_name' => 'Example']
        );

        $this->actingAs($viewer)
            ->get(route('admin.activity-log'))
            ->assertOk()
            ->assertSee('Test Administrator')
            ->assertSee('TEST-STUDENT-123');
    }

    public function test_dashboard_displays_the_signed_in_administrators_name(): void
    {
        $viewer = User::create([
            'name' => 'Reviewing Administrator',
            'email' => 'reviewing-admin@example.test',
            'role' => 'admin',
            'password' => 'Password12345',
            'is_active' => true,
        ]);

        $this->actingAs($viewer)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Welcome, Reviewing Administrator!');
    }
}
