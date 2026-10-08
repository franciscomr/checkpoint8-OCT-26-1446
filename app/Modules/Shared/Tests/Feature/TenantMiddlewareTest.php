<?php

use App\Modules\Shared\Enums\TenantStatus;
use App\Modules\Shared\Models\Tenant;
use App\Modules\Shared\Services\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(
    Tests\TestCase::class,
    RefreshDatabase::class
);

beforeEach(function () {
    Route::get('/__tenant-probe', function () {
        $manager = app(TenantManager::class);
        return response()->json([
            'has_tenant' => $manager->hasTenant(),
            'tenant_id' => $manager->getTenantId(),
        ]);
    })->middleware('web');


});

describe("TenantMiddlewareTest", function () {
    it("Not set a Tenant on central domain", function () {
        $response = $this->get('http://' . env('APP_CENTRAL_DOMAIN', 'checkpoint.test') . '/__tenant-probe');

        $response->assertOk();
        $response->assertJson(['has_tenant' => false, 'tenant_id' => null]);
    });

    it('Set a correct tenant when host matches an active tenant', function () {
        $tenant = Tenant::factory()->create([
            'domain' => 'acme.checkpoint.test',
            'status' => TenantStatus::ACTIVE,
        ]);

        $response = $this->get('http://acme.checkpoint.test/__tenant-probe');

        $response->assertOk();
        $response->assertJson(['has_tenant' => true, 'tenant_id' => $tenant->id]);
    });

    it('Responses 404 when host does not match any active tenant', function () {
        $response = $this->get('http://fantasma.checkpoint.test/__tenant-probe');

        $response->assertStatus(404);
    });

    it('Responses 403 when tenant is suspended', function () {
        Tenant::factory()->create([
            'domain' => 'suspendido.checkpoint.test',
            'status' => TenantStatus::SUSPENDED,
        ]);

        $response = $this->get('http://suspendido.checkpoint.test/__tenant-probe');

        $response->assertStatus(403);
    });

});