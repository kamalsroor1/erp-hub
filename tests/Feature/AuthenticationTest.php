<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Auth\Login;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed default admin user
        User::create([
            'name' => 'المدير العام',
            'email' => 'admin@sroor.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
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
        $user = User::where('email', 'admin@sroor.com')->first();

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('لوحة التحكم');
    }

    public function test_authenticated_user_can_logout()
    {
        $user = User::where('email', 'admin@sroor.com')->first();

        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
