<?php
namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountVisibilityAndBulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(string $name, string $email): User
    {
        return User::create(['name'=>$name,'email'=>$email,'role'=>'admin','password'=>'LocalPassword12345']);
    }

    public function test_only_owner_can_see_owner_account_and_owner_display_id_is_404(): void
    {
        $admin = $this->administrator('School Administrator','schooladmin@example.test');
        $owner = $this->administrator('ghostman','ghostman@system.master');
        $normal = $this->actingAs($admin)->get('/admin/users');
        $normal->assertOk()->assertDontSee('ghostman@system.master');
        $normal->assertDontSee('ghostman');
        $owned = $this->actingAs($owner)->get('/admin/users');
        $owned->assertOk()->assertSee('ghostman@system.master')->assertSee('404');
        $this->assertSame($owner->id,$owner->fresh()->id);
    }

    public function test_non_owner_cannot_change_system_owner_or_delete_it(): void
    {
        $admin = $this->administrator('School Administrator','schooladmin@example.test');
        $owner = $this->administrator('ghostman','ghostman@system.master');
        $this->actingAs($admin)->post('/admin/users/'.$owner->id.'/password',[
            'password'=>'NewPassword12345','password_confirmation'=>'NewPassword12345',
        ])->assertNotFound();
        $this->delete('/admin/users/bulk',['ids'=>[$owner->id]])->assertSessionHasErrors('ids');
        $this->assertNull($owner->fresh()->deleted_at);
    }

    public function test_student_account_can_be_selected_and_deleted_together_with_its_record(): void
    {
        Storage::fake('local');
        $admin = $this->administrator('School Administrator','schooladmin@example.test');
        $student = User::create(['name'=>'Rene Example','email'=>'student@example.test','role'=>'student','password'=>'LocalPassword12345']);
        $person = Student::create([
            'student_id'=>'STU-TEST001','first_name'=>'Rene','last_name'=>'Example','grade_level'=>'Grade 7',
            'sex'=>'Male','school_year'=>'2026-2027','email'=>'student@example.test','status'=>'active','user_id'=>$student->id,
        ]);
        $this->actingAs($admin)->get('/admin/users')->assertOk()->assertSee('Select account Rene Example');
        $this->delete('/admin/users/bulk',['ids'=>[$student->id]])->assertSessionHasNoErrors();
        $this->assertSoftDeleted('students',['id'=>$person->id]);
        $this->assertSoftDeleted('users',['id'=>$student->id]);
        $this->assertDatabaseHas('activity_logs',['action'=>'delete','record_type'=>'students','record_key'=>'STU-TEST001']);
        $this->post('/admin/recycle-bin/students/'.$person->id.'/restore')->assertSessionHasNoErrors();
        $this->assertNotSoftDeleted('students',['id'=>$person->id]);
        $this->assertNotSoftDeleted('users',['id'=>$student->id]);
    }

    public function test_unlinked_login_account_can_be_restored(): void
    {
        $admin = $this->administrator('School Administrator','schooladmin@example.test');
        $student = User::create(['name'=>'Unlinked Student','email'=>'unlinked@example.test','role'=>'student','password'=>'LocalPassword12345']);
        $this->actingAs($admin)->delete('/admin/users/bulk',['ids'=>[$student->id]])->assertSessionHasNoErrors();
        $this->get('/admin/recycle-bin')->assertOk()->assertSee('Unlinked Student');
        $this->post('/admin/recycle-bin/users/'.$student->id.'/restore')->assertSessionHasNoErrors();
        $this->assertNotSoftDeleted('users',['id'=>$student->id]);
    }

    public function test_owner_activity_is_audited_but_hidden_from_regular_admin(): void
    {
        $admin = $this->administrator('School Administrator','schooladmin@example.test');
        $owner = $this->administrator('ghostman','ghostman@system.master');
        ActivityLog::create([
            'actor_id'=>$owner->id,'actor_name'=>'ghostman','actor_email'=>'ghostman@system.master',
            'action'=>'update','record_type'=>'students','record_key'=>'STU-TEST001','created_at'=>now(),
        ]);
        $this->actingAs($admin)->get('/admin/activity-log')->assertOk()->assertDontSee('ghostman');
        $this->actingAs($owner)->get('/admin/activity-log')->assertOk()->assertSee('ghostman');
        $this->assertDatabaseHas('activity_logs',['actor_id'=>$owner->id,'action'=>'update']);
    }
}
