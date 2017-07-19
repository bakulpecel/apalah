<?php

namespace App\Transformers;

use App\Models\LessonPart;
use League\Fractal\TransformerAbstract;

class LessonPartTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(LessonPart $lessonPart)
    {
        return [
            'title'     => $lessonPart->title,
            'slug'      => $lessonPart->slug,
            'url_video' => $lessonPart->url_video,
            'duration'  => $lessonPart->duration,
            'views'     => $lessonPart->views,
            'created'   => $lessonPart->created_at->diffForHumans(),
        ];
    }
}
