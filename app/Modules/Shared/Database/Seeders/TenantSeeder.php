<?php

namespace App\Modules\Shared\Database\Seeders;

use App\Modules\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::create([
            'name' => 'Checkpoint Test',
            'slug' => 'checkpoint-test',
            'domain' => 'acme.checkpoint.test',
        ]);
    }
}
