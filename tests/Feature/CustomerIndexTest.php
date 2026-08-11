<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Livewire\CustomerIndex;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

class CustomerIndexTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }

    public function test_can_create_customer_with_opening_balance_without_500(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CustomerIndex::class)
            ->call('openCreateModal')
            ->set('name', 'عميل جديد ممتاز')
            ->set('phone', '01012345678')
            ->set('address', 'القاهرة - المعادي')
            ->set('opening_balance', '500.000')
            ->set('notes', 'عميل جملة منتظم')
            ->call('saveCustomer')
            ->assertHasNoErrors()
            ->assertSet('showCustomerModal', false);

        $this->assertDatabaseHas('customers', [
            'name'            => 'عميل جديد ممتاز',
            'phone'           => '01012345678',
            'current_balance' => '500.000',
        ]);
    }

    public function test_can_edit_customer_without_500(): void
    {
        $this->actingAs($this->user);

        $customer = Customer::create([
            'name'            => 'عميل قديم',
            'phone'           => '01000000000',
            'current_balance' => '0.000',
            'is_active'       => true,
        ]);

        Livewire::test(CustomerIndex::class)
            ->call('openEditModal', $customer->id)
            ->assertSet('isEditMode', true)
            ->assertSet('name', 'عميل قديم')
            ->set('name', 'عميل تم تعديل اسمه')
            ->call('saveCustomer')
            ->assertHasNoErrors();

        $customer->refresh();
        $this->assertEquals('عميل تم تعديل اسمه', $customer->name);
    }
}
