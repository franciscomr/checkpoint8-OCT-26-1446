<?php

namespace App\Modules\Shared\Contracts;

interface RendersInertiaErrorInterface
{
    public function status(): int;
    public function userMessage(): string;
}
