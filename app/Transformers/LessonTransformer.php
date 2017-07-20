<?php

namespace App\Transformers;

use App\Models\Lesson;
use League\Fractal\TransformerAbstract;

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
            'thumbnail'       => route('image.show', ['image' => $lesson->thumbnail ?? 'default.jpg']),
            'categories'      => route('category', ['lesson' => $lesson->slug]),
            'url_source_code' => $lesson->url_source_code,
            'type'            => $lesson->type ? 'premium' : 'free',
            'status'          => $lesson->status ? 'publish' : 'draft',
            'owner'           => $lesson->user->username,
            'published'       => $lesson->published_at,
        ];
    }
}
