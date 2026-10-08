<?php

namespace App\Modules\Shared\Services;

use App\Modules\Shared\Contracts\TenantResolverInterface;
use App\Modules\Shared\Exceptions\TenantException;
use App\Modules\Shared\Models\Tenant;
use Illuminate\Http\Request;
class DomainTenantResolver implements TenantResolverInterface
{
    public function __construct(
        protected Request $request
    ) {
    }
    public function resolve(): ?Tenant
    {
        $host = $this->request->getHost();

        if (blank($host)) {
            throw TenantException::tenantHostMissing();
        }

        if ($host === config('app.central_domain')) {
            return null; // dominio central: válido, sin tenant
        }

        // Busca el tenant por la columna que almacene el dominio o subdominio
        $tenant = Tenant::query()->where('domain', $host)->first();

        if (!$tenant) {
            throw TenantException::tenantNotFound($host);
        }

        if (!$tenant->isActive()) {
            throw TenantException::tenantSuspended();
        }

        return $tenant;
    }
}
