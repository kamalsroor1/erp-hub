<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Domain;

class EnsureTenantDomainsSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::find('tenant_sroor');
        if ($tenant) {
            $domains = [
                'sroor.localhost',
                'sroor.makhzani.test',
                'sroor.test',
            ];

            foreach ($domains as $d) {
                Domain::firstOrCreate([
                    'domain' => $d,
                ], [
                    'tenant_id' => $tenant->id,
                ]);
            }

            $this->command->info("Registered domains for tenant_sroor: " . $tenant->domains()->pluck('domain')->implode(', '));
        }
    }
}
