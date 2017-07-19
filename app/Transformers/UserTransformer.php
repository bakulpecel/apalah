<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'name'       => $user->name,
            'username'   => $user->username,
            'email'      => $user->email,
            'photo'      => $user->photo,
            'role'       => $user->role->role,
            'active'     => $user->active ? true : false,
            'registered' => $user->created_at->diffForHumans(),
        ];
    }
}
