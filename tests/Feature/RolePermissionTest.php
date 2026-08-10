<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected User $accountant;
    protected User $storekeeper;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);
        $storekeeperRole = Role::firstOrCreate(['name' => 'storekeeper']);

        $this->admin = User::factory()->create([
            'phone' => '01012316954',
            'is_active' => true,
        ]);
        $this->admin->assignRole($adminRole);

        $this->cashier = User::factory()->create([
            'phone' => '01111111111',
            'is_active' => true,
        ]);
        $this->cashier->assignRole($cashierRole);

        $this->accountant = User::factory()->create([
            'phone' => '01222222222',
            'is_active' => true,
        ]);
        $this->accountant->assignRole($accountantRole);

        $this->storekeeper = User::factory()->create([
            'phone' => '01333333333',
            'is_active' => true,
        ]);
        $this->storekeeper->assignRole($storekeeperRole);
    }

    public function test_admin_can_access_user_manager_and_reports()
    {
        $this->actingAs($this->admin);

        $this->get(route('users.index'))->assertStatus(200);
        $this->get(route('reports.index'))->assertStatus(200);
        $this->get(route('invoices.create'))->assertStatus(200);
        $this->get(route('daily.journal'))->assertStatus(200);
    }

    public function test_cashier_can_access_pos_and_daily_journal()
    {
        $this->actingAs($this->cashier);

        $this->get(route('invoices.create'))->assertStatus(200);
        $this->get(route('invoices.index'))->assertStatus(200);
        $this->get(route('daily.journal'))->assertStatus(200);
    }

    public function test_cashier_is_forbidden_from_user_manager_and_profit_reports()
    {
        $this->actingAs($this->cashier);

        $this->get(route('users.index'))->assertStatus(403);
        $this->get(route('reports.index'))->assertStatus(403);
    }

    public function test_accountant_can_access_reports_but_not_user_manager()
    {
        $this->actingAs($this->accountant);

        $this->get(route('reports.index'))->assertStatus(200);
        $this->get(route('daily.journal'))->assertStatus(200);
        $this->get(route('users.index'))->assertStatus(403);
    }
}
