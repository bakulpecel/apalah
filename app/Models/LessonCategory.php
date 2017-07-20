<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonCategory extends Model
{
    protected $table = 'lesson_category';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lesson_id', 'category_id',
    ];

    public $timestamps = false;

    public function category()
    {
        return $this->belongsTo(Category::class)->select(['slug', 'category']);
    }
}
