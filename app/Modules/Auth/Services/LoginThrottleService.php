<?php

namespace App\Modules\Auth\Services;

use App\Modules\Shared\Services\TenantManager;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Auth\Events\Lockout;

class LoginThrottleService
{
    protected int $maxAttempts;
    protected int $decaySeconds;
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected TenantManager $tenantManager
    ) {
        $this->maxAttempts = (int) config('auth.login_throttle.max_attempts');
        $this->decaySeconds = (int) config('auth.login_throttle.decay_seconds');
    }

    public function ensureIsNotRateLimited(string $email, string $ip): void
    {
        $key = $this->throttleKey($email, $ip);

        if (!RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            return;
        }

        event(new Lockout(request()));

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => RateLimiter::availableIn($key),
            ]),
        ]);
    }

    public function hit(string $email, string $ip): void
    {
        RateLimiter::hit($this->throttleKey($email, $ip), $this->decaySeconds);
    }

    public function clear(string $email, string $ip): void
    {
        RateLimiter::clear($this->throttleKey($email, $ip));
    }

    protected function throttleKey(string $email, string $ip): string
    {
        return sha1(implode('|', [
            $this->tenantManager->getTenantId() ?? 'no-tenant',
            strtolower($email),
            $ip,
        ]));
    }
}
