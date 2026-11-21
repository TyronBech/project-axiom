<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class UserService
{
    public function list(array $filters): Collection
    {
        $users = User::query();

        if (isset($filters['name'])) {
            $users->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['email'])) {
            $users->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (isset($filters['status'])) {
            $users->where('status', $filters['status']);
        }

        return $users->get();
    }
}
