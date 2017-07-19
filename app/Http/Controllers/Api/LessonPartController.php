<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonPart;
use App\Transformers\LessonPartTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LessonPartController extends Controller
{
    public function store(Request $request, $slug)
    {
        $lesson = Lesson::where('slug', $slug)
            ->first();

        if (!$lesson) {
            return $this->resJsonError('Terjadi kesalahan!.', 400);
        }

        $validator = Validator::make($request->all(), [
            'title'     => 'required|min:4',
            'url_video' => 'required|active_url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code'    => 400,
                    'message' => $validator->errors(),
                ],
            ], 400);
        }

        $lessonPart = LessonPart::create([
            'title'     => $request->title,
            'slug'      => str_replace(' ', '-', strtolower($request->title . ' ' . str_random(8))),
            'url_video' => $request->url_video,
            'lesson_id' => $lesson->id,
        ]);

        $response = fractal()
            ->item($lessonPart)
            ->transformWith(new LessonPartTransformer)
            ->toArray();

        return response()
            ->json($response, 201);
    }
}
