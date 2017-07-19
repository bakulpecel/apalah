<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LessonPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Lesson $lesson)
    {
        return $user->ownsLesson($lesson);
    }

    public function delete(User $user, Lesson $lesson)
    {
        return $user->ownsLesson($lesson);
    }
}
