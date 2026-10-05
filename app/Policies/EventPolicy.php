<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

/**
 * Semua akun aktif (admin & panitia) boleh melihat event.
 * Hanya admin yang boleh membuat, mengubah, dan menghapus event.
 */
class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Event $event): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Event $event): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->isAdmin();
    }
}
