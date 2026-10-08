<?php

use App\Models\User;
use App\Modules\Shared\Enums\TenantStatus;
use App\Modules\Shared\Exceptions\TenantException;
use App\Modules\Shared\Models\Tenant;
use App\Modules\Shared\Services\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(
    Tests\TestCase::class,
    RefreshDatabase::class
);


function createTenant(string $domain): Tenant
{
    return Tenant::factory()->create([
        'domain' => $domain,
    ]);
}

describe('BelongsToTenantTest', function () {
    it('create a tenant and assign it to a user', function () {
        $acme = createTenant('acme.checkpoint.test');
        app(TenantManager::class)->setTenant($acme);

        $user = User::create([
            'name' => 'Ada',
            'email' => 'ada@acme.test',
            'password' => bcrypt('password'),
        ]);

        expect($user->tenant_id)->toBe($acme->id);
    });


    it('respects an explicit tenant_id instead of overwriting it, if there is an active tenant context', function () {
        $acme = createTenant('acme.checkpoint.test');
        $globex = createTenant('globex.checkpoint.test');

        app(TenantManager::class)->setTenant($acme);
        $user = User::factory()->forTenant($globex)->create(); //Se puede crear un usuario con un tenant distinto al que está activo en el contexto 
        //  dd($user->tenant_id . ' - ' . $globex->id . ' -' . $acme->id);
        expect($user->tenant_id)->toBe($globex->id);
    });


    it('Throws an exception when trying to create a user without a tenant context', function () {
        $acme = createTenant('acme.checkpoint.test');

        app(TenantManager::class)->forgetTenant();

        expect(fn() => User::create([
            'name' => 'Nadie',
            'email' => 'nadie@nowhere.test',
            'password' => bcrypt('password'),
            'tenant_id' => $acme->id,
        ]))->toThrow(TenantException::class);
    });

    it('Global scope only filter users within the same active tenant', function () {
        $acme = createTenant('acme.checkpoint.test');
        $globex = createTenant('globex.checkpoint.test');
        app(TenantManager::class)->setTenant($acme);
        User::factory()->forTenant($acme)->create(['email' => 'ada@acme.test']);
        User::factory()->forTenant($acme)->create(['email' => 'grace@acme.test']);

        app(TenantManager::class)->setTenant($globex);
        User::factory()->forTenant($globex)->create(['email' => 'homer@globex.test']);

        app(TenantManager::class)->setTenant($acme);
        $emails = User::all()->pluck('email')->all();

        expect($emails)->toContain('ada@acme.test', 'grace@acme.test')
            ->and($emails)->not->toContain('homer@globex.test')
            ->and($emails)->toHaveCount(2);
    });

    it('Throws an exception when querying without an active tenant context', function () {
        $acme = createTenant('acme.checkpoint.test');
        app(TenantManager::class)->setTenant($acme);

        User::factory()->forTenant($acme)->create();

        app(TenantManager::class)->forgetTenant();

        expect(fn() => User::all())->toThrow(TenantException::class);
    });

    it('Can create users using same email in different tenants', function () {
        $acme = createTenant('acme.checkpoint.test');
        $globex = createTenant('globex.checkpoint.test');

        app(TenantManager::class)->setTenant($acme);
        User::factory()->create(['email' => 'mismo@correo.test']);

        app(TenantManager::class)->setTenant($globex);
        $user = User::factory()->create(['email' => 'mismo@correo.test']);

        expect($user->email)->toBe('mismo@correo.test')
            ->and($user->tenant_id)->toBe($globex->id);
    });

    it('The relationship returns the correct tenant', function () {
        $acme = createTenant('acme.checkpoint.test');

        app(TenantManager::class)->setTenant($acme);
        $user = User::factory()->create();

        expect($user->tenant->is($acme))->toBeTrue();
    });

});