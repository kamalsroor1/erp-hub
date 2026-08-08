<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Profile;
use App\Livewire\Auth\UserManager;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'cashier']);

        $this->admin = User::create([
            'name' => 'كمال سرور - المدير العام',
            'email' => 'admin@sroor.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->admin->assignRole('admin');
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard()
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_when_accessing_items_page()
    {
        $response = $this->get('/items');
        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_when_accessing_pos_invoice_create()
    {
        $response = $this->get('/invoices/create');
        $response->assertRedirect('/login');
    }

    public function test_login_page_renders_successfully()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('تسجيل الدخول');
        $response->assertSee('admin@sroor.com');
    }

    public function test_user_can_login_with_valid_credentials_via_livewire()
    {
        Livewire::test(Login::class)
            ->set('email', 'admin@sroor.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_wrong_password()
    {
        Livewire::test(Login::class)
            ->set('email', 'admin@sroor.com')
            ->set('password', 'wrong-password-123')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_access_dashboard()
    {
        $response = $this->actingAs($this->admin)->get('/');
        $response->assertStatus(200);
        $response->assertSee('لوحة التحكم');
    }

    public function test_authenticated_user_can_update_profile_info()
    {
        $this->actingAs($this->admin);

        Livewire::test(Profile::class)
            ->set('name', 'كمال سرور المعدل')
            ->set('email', 'kamal.updated@sroor.com')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'name' => 'كمال سرور المعدل',
            'email' => 'kamal.updated@sroor.com',
        ]);
    }

    public function test_authenticated_user_can_update_password()
    {
        $this->actingAs($this->admin);

        Livewire::test(Profile::class)
            ->set('current_password', 'password')
            ->set('new_password', 'newsecret123')
            ->set('new_password_confirmation', 'newsecret123')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->admin->refresh();
        $this->assertTrue(Hash::check('newsecret123', $this->admin->password));
    }

    public function test_admin_can_create_new_cashier_user()
    {
        $this->actingAs($this->admin);

        Livewire::test(UserManager::class)
            ->call('openCreateModal')
            ->set('name', 'كاشير مسائي جديد')
            ->set('email', 'cashier.night@sroor.com')
            ->set('password', 'cashier123')
            ->set('role', 'cashier')
            ->set('is_active', true)
            ->call('saveUser')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'كاشير مسائي جديد',
            'email' => 'cashier.night@sroor.com',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_toggle_user_active_status()
    {
        $this->actingAs($this->admin);

        $cashier = User::create([
            'name' => 'كاشير للتجربة',
            'email' => 'test.cashier@sroor.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        Livewire::test(UserManager::class)
            ->call('toggleUserStatus', $cashier->id);

        $this->assertDatabaseHas('users', [
            'id' => $cashier->id,
            'is_active' => false,
        ]);
    }

    public function test_authenticated_user_can_logout()
    {
        $response = $this->actingAs($this->admin)->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
