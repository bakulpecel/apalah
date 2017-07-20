<?php

namespace App\Transformers;

use App\Models\ArticleCategory;
use League\Fractal\TransformerAbstract;

class ArticleCategoryTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(ArticleCategory $articleCategory)
    {
        return [
            'category' => $articleCategory->category,
        ];
    }
}
