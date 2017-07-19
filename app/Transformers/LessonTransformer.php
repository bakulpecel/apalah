<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Lesson;

class LessonTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Lesson $lesson)
    {
        return [
            'title'           => $lesson->title,
            'slug'            => $lesson->slug,
            'summary'         => $lesson->summary,
            'parts'           => $lesson->parts,
            'thumbnail'       => $lesson->thumbnail,
            'url_source_code' => $lesson->url_source_code,
            'type'            => $lesson->type ? 'premium' : 'free',
            'status'          => $lesson->status ? 'publish' : 'draft',
            'owner'           => $lesson->user->username,
            'published'       => $lesson->published_at,
        ];
    }
}
