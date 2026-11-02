<?php
namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CsvAccountsTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        return User::create(['name'=>'School Administrator','email'=>'school-admin@example.test','password'=>'AdminPassword12345','role'=>'admin']);
    }

    public function test_admin_account_directory_displays_real_records(): void
    {
        $admin = $this->administrator();
        $this->actingAs($admin)->get('/admin/users')->assertOk()->assertSee('school-admin@example.test');
    }

    public function test_admin_csv_preview_and_import_create_non_default_password_account(): void
    {
        $this->actingAs($this->administrator());
        Storage::fake('local');
        $file = UploadedFile::fake()->createWithContent('admins.csv',"admin_id,name,email\n,Second Administrator,second@example.test\n");
        $this->post('/admin/imports/admins/preview',['file'=>$file])->assertOk()->assertSee('Second Administrator');
        $this->assertDatabaseMissing('users',['email'=>'second@example.test']);
        $token = session('school_import.token');
        $this->post('/admin/imports/admins/confirm',['token'=>$token,'mode'=>'add_only','lines'=>[2]])->assertRedirect('/admin/imports/admins');
        $record = User::where('email','second@example.test')->firstOrFail();
        $this->assertSame('admin',$record->role);
        $this->assertTrue(Hash::isHashed($record->password));
        $this->assertDatabaseHas('activity_logs',['action'=>'csv_import','record_type'=>'admins']);
        Storage::disk('local')->assertExists('school-records/admins.csv');
    }

    public function test_password_update_never_writes_password_or_hash_into_csv_or_activity_log(): void
    {
        $admin = $this->administrator();
        Storage::fake('local');
        $this->actingAs($admin)->post('/admin/users/'.$admin->id.'/password',[
            'password'=>'NewLocalPassword123','password_confirmation'=>'NewLocalPassword123',
        ])->assertRedirect();
        $this->assertTrue(Hash::check('NewLocalPassword123',$admin->fresh()->password));
        $this->assertDatabaseHas('activity_logs',['action'=>'password_reset','record_type'=>'users']);
        $this->assertStringNotContainsString('NewLocalPassword123',ActivityLog::latest('id')->first()->toJson());
        $response = $this->get('/admin/exports/admins');
        $response->assertOk();
        $contents = $response->streamedContent();
        $this->assertStringNotContainsString('NewLocalPassword123',$contents);
        $this->assertStringNotContainsString($admin->fresh()->password,$contents);
    }

    public function test_csv_import_duplicate_admin_emails_are_flagged(): void
    {
        $this->actingAs($this->administrator());
        Storage::fake('local');
        $file = UploadedFile::fake()->createWithContent('admins.csv',"admin_id,name,email\n,First,duplicate@example.test\n,Second,duplicate@example.test\n");
        $this->post('/admin/imports/admins/preview',['file'=>$file])->assertOk()->assertSee('Duplicate email in uploaded CSV');
        $this->assertDatabaseMissing('users',['email'=>'duplicate@example.test']);
    }

    public function test_student_save_refreshes_private_csv(): void
    {
        $this->actingAs($this->administrator());
        Storage::fake('local');
        $this->postJson('/admin/students',[
            'student_id'=>'STU-5001','first_name'=>'Rene','middle_name'=>null,'last_name'=>'Sample',
            'grade_level'=>'Grade 7','section'=>'A','sex'=>'Male','school_year'=>'2026-2027',
            'email'=>'rene.student5001@example.test','status'=>'active',
        ])->assertOk();
        Storage::disk('local')->assertExists('school-records/students.csv');
        $this->assertStringContainsString('STU-5001',Storage::disk('local')->get('school-records/students.csv'));
    }
}
