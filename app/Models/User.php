<?php

namespace App\Models;

use App\Models\Lesson;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'email', 'password', 'photo', 'role_id', 'api_token', 'remember_token', 'active',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'api_token', 'remember_token',
    ];

    public $timestamps = true;

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function ownsLesson(Lesson $lesson)
    {
        if (auth()->user()->role_id === 1) {
            return true;
        }

        if (auth()->id() === $lesson->user->id) {
            return true;
        }

        return false;
    }
}
