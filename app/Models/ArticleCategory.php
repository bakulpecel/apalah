<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleCategory extends Model
{
    protected $table = 'article_category';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'article_id', 'category_id',
    ];

    public $timestamps = false;

    public function category()
    {
        return $this->belongsTo(Category::class)->select(['slug', 'category']);
    }
}
