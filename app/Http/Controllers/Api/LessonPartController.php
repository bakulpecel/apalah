<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonPart;
use App\Models\PremiumUser;
use App\Transformers\LessonPartTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LessonPartController extends Controller
{
    public function guestIndex($slug)
    {
        $lesson = Lesson::where('slug', $slug)
            ->first();

        if (!$lesson) {
            return $this->resJsonError('Pelajaran tidak ditemukan', 404);
        }

        $premiumUser = PremiumUser::where('user_id', Auth::user()->id)
            ->where('end_at', '>', Carbon::today('Asia/Jakarta')->toDateTimeString())
            ->first();

        if ($lesson->type === 1) {
            if (Auth()->user()->role_id === 1 || $premiumUser || $lesson->user_id === Auth::user()->id) {
                true;
            } else {
                return $this->resJsonError('Anda harus menjadi premium member!', 403);
            }
        }

        $lessonPart = LessonPart::where('lesson_id', $lesson->id)
            ->get();

        $response = fractal()
            ->collection($lessonPart)
            ->transformWith(new LessonPartTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function guestShow($slug, $slugPart)
    {
        $lesson = Lesson::where('slug', $slug)
            ->first();

        if (!$lesson) {
            return $this->resJsonError('Pelajaran tidak ditemukan', 404);
        }

        $premiumUser = PremiumUser::where('user_id', Auth::user()->id)
            ->where('end_at', '>', Carbon::today('Asia/Jakarta')->toDateTimeString())
            ->first();

        if ($lesson->type === 1) {
            if (Auth::user()->role_id === 1 || $premiumUser || $lesson->user_id === Auth::user()->id) {
                true;
            } else {
                return $this->resJsonError('Anda harus menjadi premium member!', 403);
            }
        }

        $lessonPart = LessonPart::where('lesson_id', $lesson->id)
            ->where('slug', $slugPart)
            ->first();

        if (!$lessonPart) {
            return $this->resJsonError('Kurikulum tidak ditemukan', 404);
        }

        $response = fractal()
            ->item($lessonPart)
            ->transformWith(new LessonPartTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function store(Request $request, $slug)
    {
        $lesson = Lesson::where('slug', $slug)
            ->first();

        if (!$lesson) {
            return $this->resJsonError('Terjadi kesalahan!.', 400);
        }

        $this->authorize('create', $lesson);

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

    public function update(Request $request, $slug, $slugPart)
    {
        $lesson = Lesson::where('slug', $slug)
            ->first();

        if (!$lesson) {
            return $this->resJsonError('Terjadi kesalahan!.', 400);
        }

        $lessonPart = lessonPart::where('lesson_id', $lesson->id)
            ->where('slug', $slugPart)
            ->first();

        if (!$lessonPart) {
            return $this->resJsonError('Terjadi kesalahan!.', 400);
        }

        $this->authorize('update', $lesson);

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

        $lessonPart->update([
            'title'     => $request->title,
            'slug'      => str_replace(' ', '-', strtolower($request->title . ' ' . str_random(8))),
            'url_video' => $request->url_video,
        ]);

        $response = fractal()
            ->item($lessonPart)
            ->transformWith(new LessonPartTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function destroy($slug, $slugPart)
    {
        $lesson = Lesson::where('slug', $slug)
            ->first();

        if (!$lesson) {
            return $this->resJsonError('Terjadi kesalahan!.', 400);
        }

        $lessonPart = LessonPart::where('lesson_id', $lesson->id)
            ->where('slug', $slugPart)
            ->first();

        if (!$lessonPart) {
            return $this->resJsonError('Terjadi kesalahan!.', 400);
        }

        $this->authorize('delete', $lesson);

        $lessonPart->delete();

        return $this->resJsonSuccess('Kurikulum berhasil dihapus.', 200);
    }
}
