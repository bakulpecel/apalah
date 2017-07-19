<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonPart extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'slug', 'url_video', 'duration', 'views', 'lesson_id',
    ];

    public $timestamps = true;
}
