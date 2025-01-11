<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Website;
use Illuminate\Auth\Access\HandlesAuthorization;

class WebsitePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Website $website)
    {
        return $user->id === $website->user_id;
    }

    public function update(User $user, Website $website)
    {
        return $user->id === $website->user_id;
    }

    public function delete(User $user, Website $website)
    {
        return $user->id === $website->user_id;
    }
}
