<?php

namespace App\Transformers;

use App\Models\Article;
use League\Fractal\TransformerAbstract;

class ArticleTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Article $article)
    {
        return [
            'title'      => $article->title,
            'slug'       => $article->slug,
            'content'    => $article->content,
            'thumbnail'  => route('image.show', ['image' => $article->thumbnail ?? 'default.jpg']),
            'categories' => route('category', ['article' => $article->slug]),
            'status'     => $article->status ? 'publish' : 'draft',
            'owner'      => $article->user->username,
            'published'  => $article->published_at,
        ];
    }
}
