<?php

namespace App\Modules\Shared\Concerns;

use App\Modules\Shared\Exceptions\TenantException;
use App\Modules\Shared\Models\Tenant;
use App\Modules\Shared\Scopes\TenantScope;
use App\Modules\Shared\Services\TenantManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin Model
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        /** @var Model|string $modelClass */
        $modelClass = static::class;
        $modelClass::addGlobalScope(new TenantScope());

        $modelClass::creating(function (Model $model) {
            $tenantManager = app(TenantManager::class);

            if (!$tenantManager->hasTenant()) {
                throw TenantException::contextNotSet(static::class);
            }

            if (!$model->getAttribute('tenant_id')) {
                $model->setAttribute('tenant_id', $tenantManager->getTenantId());
            }
        });
    }

    public function tenant(): BelongsTo
    {
        /** @var Model|self $this */
        return $this->belongsTo(Tenant::class);
    }
}
