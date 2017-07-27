<?php

namespace App\Models;

use Carbon\Carbon;
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
        'name', 'username', 'email', 'password', 'phone_number', 'photo', 'role_id', 'api_token', 'remember_token', 'active',
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

    public function ownsArticle(Article $article)
    {
        if (auth()->user()->role_id === 1) {
            return true;
        }

        if (auth()->user()->role_id === 3) {
            return true;
        }

        if (auth()->id() === $article->user->id) {
            return true;
        }

        return false;
    }
}
