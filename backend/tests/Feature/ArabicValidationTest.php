<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Livewire\CustomerIndex;
use App\Livewire\ItemIndex;
use App\Livewire\StoreIndex;
use App\Livewire\InvoiceCreate;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

class ArabicValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_validation_messages_are_translated_into_arabic(): void
    {
        $this->actingAs($this->admin);

        // Test customer creation validation error message
        Livewire::test(CustomerIndex::class)
            ->call('openCreateModal')
            ->set('name', '')
            ->call('saveCustomer')
            ->assertHasErrors(['name' => 'required'])
            ->assertSee('حقل الاسم مطلوب ولا يمكن تركه فارغاً.');

        // Test item creation validation error message
        Livewire::test(ItemIndex::class)
            ->call('openCreateModal')
            ->set('name', '')
            ->set('code', '')
            ->call('saveItem')
            ->assertHasErrors(['name' => 'required', 'code' => 'required'])
            ->assertSee('حقل الاسم مطلوب ولا يمكن تركه فارغاً.')
            ->assertSee('حقل كود الصنف / الكود التعريفي مطلوب ولا يمكن تركه فارغاً.');

        // Test store creation validation error message
        Livewire::test(StoreIndex::class)
            ->call('openCreateModal')
            ->set('name', '')
            ->set('code', '')
            ->call('saveStore')
            ->assertHasErrors(['name' => 'required', 'code' => 'required'])
            ->assertSee('حقل الاسم مطلوب ولا يمكن تركه فارغاً.');
    }
}
