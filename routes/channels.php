<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}', function (User $user, int $userId): bool {
    return (int) $user->id === (int) $userId && (bool) $user->is_active;
});