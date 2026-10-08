<?php

namespace App\Modules\Shared\Scopes;
use App\Modules\Shared\Exceptions\TenantException;
use App\Modules\Shared\Services\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantManager = app(TenantManager::class);

        if (!$tenantManager->hasTenant()) {
            throw TenantException::contextNotSet($model::class);
        }

        $builder->where($model->getTable() . '.tenant_id', $tenantManager->getTenantId());
    }
}
