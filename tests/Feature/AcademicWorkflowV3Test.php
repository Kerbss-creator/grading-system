<?php

namespace Tests\Feature;

use App\Models\GradeRecord;
use App\Models\ClassAdviser;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectOffering;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AcademicWorkflowV3Test extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['name'=>'Academic Admin','role'=>'admin','is_active'=>true,'username'=>'admin_test']);
    }

    private function classroom(): array
    {
        $teacherUser = User::factory()->create(['role'=>'teacher','is_active'=>true,'username'=>'teacher_test']);
        $studentUser = User::factory()->create(['role'=>'student','is_active'=>true,'username'=>'student_test']);
        $teacher = Teacher::create(['teacher_id'=>'TE-TEST','first_name'=>'Test','last_name'=>'Teacher',
            'email'=>$teacherUser->email,'subject'=>'Math','school_year'=>'2025-2026','status'=>'active','user_id'=>$teacherUser->id]);
        $student = Student::create(['student_id'=>'ST-TEST','first_name'=>'Test','last_name'=>'Learner',
            'email'=>$studentUser->email,'grade_level'=>'Grade 7','section'=>'A','sex'=>'Female',
            'school_year'=>'2025-2026','status'=>'active','user_id'=>$studentUser->id]);
        $subject = Subject::create(['name'=>'Mathematics','grade_level'=>'Grade 7','school_year'=>'2025-2026','is_active'=>true]);
        $offering = SubjectOffering::create(['subject_id'=>$subject->id,'teacher_id'=>$teacher->id,
            'section'=>'A','meeting_days'=>'Mon,Wed,Fri','starts_at'=>'08:00','ends_at'=>'09:00']);
        return [$teacherUser,$studentUser,$student,$offering];
    }

    public function test_teacher_can_submit_and_admin_can_approve_linked_grade(): void
    {
        $admin = $this->admin();
        [$teacher,$studentUser,$student,$offering] = $this->classroom();
        $this->actingAs($teacher)->post(route('teacher.grades.save',$offering),[
            'student_id'=>$student->id,'quarter'=>1,'grade'=>88.5,
        ])->assertRedirect();
        $record = GradeRecord::firstOrFail();
        $this->assertSame('draft',$record->status);

        $this->actingAs($teacher)->post(route('teacher.grades.submit'),['ids'=>[$record->id]])->assertRedirect();
        $this->assertSame('pending',$record->fresh()->status);

        $this->actingAs($admin)->post(route('admin.grade-approval.review'),[
            'ids'=>[$record->id],'decision'=>'approved',
        ])->assertRedirect();
        $this->assertSame('approved',$record->fresh()->status);
        $this->actingAs($studentUser)->get(route('student.dashboard'))->assertOk()->assertSee('88.50');
    }

    public function test_teacher_cannot_review_without_assignment_or_edit_pending_record(): void
    {
        $this->admin();
        [$teacher,$studentUser,$student,$offering] = $this->classroom();
        $record = GradeRecord::create(['student_id'=>$student->id,'subject_offering_id'=>$offering->id,
            'quarter'=>1,'grade'=>90,'status'=>'pending','submitted_by'=>$teacher->id]);
        $this->actingAs($teacher)->post(route('admin.grade-approval.review'),[
            'ids'=>[$record->id],'decision'=>'approved',
        ])->assertForbidden();
        $this->actingAs($teacher)->post(route('teacher.grades.save',$offering),[
            'student_id'=>$student->id,'quarter'=>1,'grade'=>99,
        ])->assertSessionHasErrors('grade');
        $this->assertEquals(90,$record->fresh()->grade);
        $this->actingAs($studentUser)->get(route('student.dashboard'))->assertDontSee('90.00');
    }

    public function test_assigned_adviser_can_approve_other_teachers_grade_but_not_their_own(): void
    {
        $this->admin();
        [$subjectTeacher,$studentUser,$student,$offering] = $this->classroom();
        $adviserUser = User::factory()->create(['role'=>'teacher','is_active'=>true,'username'=>'teacher_adviser']);
        $adviser = Teacher::create(['teacher_id'=>'TE-ADVISER','first_name'=>'Advisory','last_name'=>'Teacher',
            'email'=>$adviserUser->email,'subject'=>'English','school_year'=>'2025-2026',
            'status'=>'active','user_id'=>$adviserUser->id]);
        ClassAdviser::create(['grade_level'=>'Grade 7','section'=>'A','school_year'=>'2025-2026','teacher_id'=>$adviser->id]);
        $record = GradeRecord::create(['student_id'=>$student->id,'subject_offering_id'=>$offering->id,
            'quarter'=>1,'grade'=>91,'status'=>'pending','submitted_by'=>$subjectTeacher->id]);
        $this->actingAs($adviserUser)->get(route('teacher.grade-approval'))->assertOk()->assertSee('91.00');
        $this->actingAs($adviserUser)->post(route('teacher.grade-approval.review'),[
            'ids'=>[$record->id], 'decision'=>'approved',
        ])->assertRedirect();
        $this->assertSame('approved',$record->fresh()->status);
        $record->update(['status'=>'pending','submitted_by'=>$adviserUser->id]);
        $this->actingAs($adviserUser)->post(route('teacher.grade-approval.review'),[
            'ids'=>[$record->id], 'decision'=>'approved',
        ])->assertForbidden();
    }

    public function test_username_is_accepted_as_login_identifier(): void
    {
        $admin = User::factory()->create(['role'=>'admin','is_active'=>true,
            'username'=>'admin_456','password'=>Hash::make('StrongPass987!')]);
        $this->post(route('login.submit'),['email'=>'admin_456','password'=>'StrongPass987!'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_student_cannot_submit_or_approve_and_no_subject_is_assumed(): void
    {
        $this->admin();
        [$teacher,$studentUser,$student,$offering] = $this->classroom();
        $this->actingAs($studentUser)->post(route('teacher.grades.save',$offering),[
            'student_id'=>$student->id,'quarter'=>1,'grade'=>99,
        ])->assertForbidden();
        $this->assertDatabaseCount('grade_records',0);
    }
}
