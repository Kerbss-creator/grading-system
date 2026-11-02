<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInterfaceRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $name, string $role): User
    {
        return User::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).'@example.test',
            'role' => $role,
            'password' => 'LocalTestingPassword1234',
        ]);
    }

    public function test_admin_root_and_dashboard_remain_available(): void
    {
        $admin = $this->userWithRole('School Administrator', 'admin');
        $this->actingAs($admin)->get('/admin')->assertRedirect('/admin/dashboard');
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertSee('Dashboard');
        $this->actingAs($admin)->get('/admin/profile')->assertRedirect('/admin/settings');
        $this->actingAs($admin)->get('/admin/settings')->assertOk();
    }

    public function test_account_role_filter_returns_only_the_selected_role(): void
    {
        $admin = $this->userWithRole('School Administrator', 'admin');
        $this->userWithRole('Visible Student', 'student');
        $this->userWithRole('Visible Teacher', 'teacher');
        $this->actingAs($admin)->get('/admin/users?role=teacher')
            ->assertOk()->assertSee('Visible Teacher')
            ->assertDontSee('Visible Student');
        $this->actingAs($admin)->get('/admin/users?role=student')
            ->assertOk()->assertSee('Visible Student')
            ->assertDontSee('Visible Teacher');
    }

    public function test_administrator_management_is_separate_and_protected(): void
    {
        $admin = $this->userWithRole('School Administrator', 'admin');
        $student = $this->userWithRole('Visible Student', 'student');
        $this->actingAs($admin)->get('/admin/admins')->assertOk()
            ->assertSee('School Administrator')->assertDontSee('Visible Student');
        $this->actingAs($student)->get('/admin/admins')->assertForbidden();
        $this->actingAs($student)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_students_and_teachers_cannot_use_each_others_dashboard(): void
    {
        $student = $this->userWithRole('Visible Student', 'student');
        $teacher = $this->userWithRole('Visible Teacher', 'teacher');
        $this->actingAs($student)->get('/teacher/dashboard')->assertForbidden();
        $this->actingAs($teacher)->get('/student/dashboard')->assertForbidden();
        $this->actingAs($teacher)->get('/teacher/dashboard')->assertOk();
        $this->actingAs($student)->get('/student/dashboard')->assertOk();
    }
}
