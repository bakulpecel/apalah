<?php

namespace App\Transformers;

use App\Models\LessonCategory;
use League\Fractal\TransformerAbstract;

class LessonCategoryTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(LessonCategory $lessonCategory)
    {
        return [
            'category' => $lessonCategory->category,
        ];
    }
}
