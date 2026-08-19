<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Actions\Tenants\GetTenantsIndexDataAction;
use App\Actions\Tenants\ProvisionTenantAction;
use App\Actions\Tenants\ToggleTenantStatusAction;
use App\Actions\Tenants\OverrideTenantFeatureAction;
use App\Actions\Plans\UpdatePlanAction;
use App\DTOs\CreateTenantDTO;
use Spatie\Permission\Models\Role;

class SuperAdminSolidTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected Plan $basicPlan;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Event::fake([
            \Stancl\Tenancy\Events\TenantCreated::class,
            \Stancl\Tenancy\Events\CreatingDatabase::class,
            \Stancl\Tenancy\Events\DatabaseCreated::class,
            \Stancl\Tenancy\Events\MigratingDatabase::class,
            \Stancl\Tenancy\Events\DatabaseMigrated::class,
        ]);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $this->superAdmin = User::factory()->create([
            'name' => 'مدير المنصة المركزي',
            'email' => 'super@test.com',
            'phone' => '01000000000',
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole($role);

        $this->basicPlan = Plan::create([
            'name' => 'الباقة الأساسية',
            'slug' => 'basic',
            'description' => 'باقة المحلات الفردية',
            'price_monthly' => '499.000',
            'price_yearly' => '4990.000',
            'max_users' => 3,
            'max_stores' => 1,
            'max_items' => 500,
            'max_invoices_per_month' => 2000,
            'is_active' => true,
            'features' => ['pos.access' => true, 'blender.access' => false],
        ]);
    }

    public function test_tenants_index_action_with_pipeline_filters(): void
    {
        Tenant::create([
            'id' => 'cairo-roastery',
            'name' => 'محامص بن القاهرة',
            'slug' => 'cairo-roastery',
            'plan_id' => $this->basicPlan->id,
            'email' => 'cairo@coffee.com',
            'phone' => '01011111111',
            'status' => 'active',
        ]);

        Tenant::create([
            'id' => 'alex-beans',
            'name' => 'مطاحن الإسكندرية',
            'slug' => 'alex-beans',
            'plan_id' => $this->basicPlan->id,
            'email' => 'alex@coffee.com',
            'phone' => '01022222222',
            'status' => 'suspended',
        ]);

        $action = app(GetTenantsIndexDataAction::class);

        // Test Filter 1: Status = active
        $requestActive = Request::create('/admin/super/tenants', 'GET', ['status' => 'active']);
        app()->instance('request', $requestActive);
        $dataActive = $action->execute($requestActive);
        $this->assertEquals(1, $dataActive['tenants']->total());
        $this->assertEquals('محامص بن القاهرة', $dataActive['tenants']->items()[0]['name']);

        // Test Filter 2: Search = الإسكندرية
        $requestSearch = Request::create('/admin/super/tenants', 'GET', ['search' => 'الإسكندرية']);
        app()->instance('request', $requestSearch);
        $dataSearch = $action->execute($requestSearch);
        $this->assertEquals(1, $dataSearch['tenants']->total());
        $this->assertEquals('مطاحن الإسكندرية', $dataSearch['tenants']->items()[0]['name']);
    }

    public function test_toggle_tenant_status_action(): void
    {
        $tenant = Tenant::create([
            'id' => 'test-store',
            'name' => 'متجر تجريبي',
            'slug' => 'test-store',
            'plan_id' => $this->basicPlan->id,
            'email' => 'store@test.com',
            'status' => 'active',
        ]);

        $action = app(ToggleTenantStatusAction::class);
        $action->execute($tenant, 'suspended', 0);

        $tenant->refresh();
        $this->assertEquals('suspended', $tenant->status);
    }

    public function test_override_tenant_feature_action(): void
    {
        $tenant = Tenant::create([
            'id' => 'test-store-2',
            'name' => 'متجر تجريبي 2',
            'slug' => 'test-store-2',
            'plan_id' => $this->basicPlan->id,
            'email' => 'store2@test.com',
            'status' => 'active',
            'enabled_features' => [],
        ]);

        $action = app(OverrideTenantFeatureAction::class);
        $action->execute($tenant, 'blender.access');

        $tenant->refresh();
        $this->assertContains('blender.access', $tenant->enabled_features);

        // Toggle again should remove override
        $action->execute($tenant, 'blender.access');
        $tenant->refresh();
        $this->assertNotContains('blender.access', $tenant->enabled_features);
    }

    public function test_update_plan_action(): void
    {
        $action = app(UpdatePlanAction::class);
        $action->execute($this->basicPlan, [
            'name' => 'الباقة الأساسية بلس',
            'price_monthly' => 599.000,
            'price_yearly' => 5990.000,
            'max_users' => 5,
            'max_stores' => 2,
            'max_items' => 1000,
            'max_invoices_per_month' => 5000,
            'is_active' => true,
            'is_popular' => true,
            'features' => ['pos.access' => true, 'blender.access' => true],
        ]);

        $this->basicPlan->refresh();
        $this->assertEquals('الباقة الأساسية بلس', $this->basicPlan->name);
        $this->assertEquals(599.0, (float)$this->basicPlan->price_monthly);
        $this->assertTrue($this->basicPlan->is_popular);
        $this->assertTrue($this->basicPlan->features['blender.access']);
    }
}
