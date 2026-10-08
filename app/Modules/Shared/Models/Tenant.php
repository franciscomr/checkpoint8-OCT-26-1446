<?php

namespace App\Modules\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Modules\Shared\Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Modules\Shared\Enums\TenantStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'domain', 'status'])]
class Tenant extends Model
{
    use HasUlids, HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'status' => TenantStatus::class
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === TenantStatus::ACTIVE;
    }

    protected static function newFactory()
    {
        return TenantFactory::new();
    }
}
