<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonPart;
use App\Models\PremiumUser;
use App\Transformers\LessonPartTransformer;
use Carbon\Carbon;
use DateInterval;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Madcoda\Youtube\Facades\Youtube;

class LessonPartController extends Controller
{
    public function guestIndex($slug)
    {
        $lesson = Lesson::where('slug', $slug)
            ->where('status', 1)
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

        if (!$lessonPart) {
            return $this->resJsonError('Belum ada kurikulum', 404);
        }

        $response = fractal()
            ->collection($lessonPart)
            ->transformWith(new LessonPartTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function authIndex($slug)
    {
        $lesson = Lesson::where('slug', $slug)
            ->where('status', 1)
            ->first();

        if (!$lesson) {
            return $this->resJsonError('Pelajaran tidak ditemukan', 404);
        }

        if ($lesson->type === 1) {
            return $this->resJsonError('Anda harus menjadi premium member');
        }

        $lessonPart = LessonPart::where('lesson_id', $lesson->id)
            ->get();

        if (!$lessonPart) {
            return $this->resJsonError('Belum ada kurikulum', 404);
        }

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

        $lessonPart->views = $lessonPart->views + 1;
        $lessonPart->save();

        $response = fractal()
            ->item($lessonPart)
            ->transformWith(new LessonPartTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function authShow($slug, $slugPart)
    {
        $lesson = Lesson::where('slug', $slug)
            ->where('status', 1)
            ->first();

        if (!$lesson) {
            return $this->resJsonError('Pelajaran tidak ditemukan', 404);
        }

        if ($lesson->type === 1) {
            return $this->resJsonError('Anda harus menjadi premium member');
        }

        $lessonPart = LessonPart::where('lesson_id', $lesson->id)
            ->where('slug', $slugPart)
            ->first();

        if (!$lessonPart) {
            return $this->resJsonError('Belum ada kurikulum', 404);
        }

        $lessonPart->views = $lessonPart->views + 1;
        $lessonPart->save();

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

        $videoUrl = $request->url_video;
        $queryString = [];
        parse_str(parse_url($videoUrl, PHP_URL_QUERY), $queryString);
        $videoId = $queryString['v'];
        $duration = new DateInterval(Youtube::getVideoInfo($videoId)->contentDetails->duration);

        $lessonPart = LessonPart::create([
            'title'     => $request->title,
            'slug'      => str_replace(' ', '-', strtolower($request->title . ' ' . str_random(8))),
            'url_video' => $request->url_video,
            'duration'  => $duration->format('%H:%I:%S'),
            'lesson_id' => $lesson->id,
        ]);

        $lesson->parts = $lesson->parts + 1;
        $lesson->save();

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

        $videoUrl = $request->url_video;
        $queryString = [];
        parse_str(parse_url($videoUrl, PHP_URL_QUERY), $queryString);
        $videoId = $queryString['v'];
        $duration = new DateInterval(Youtube::getVideoInfo($videoId)->contentDetails->duration);

        $lessonPart->update([
            'title'     => $request->title,
            'slug'      => str_replace(' ', '-', strtolower($request->title . ' ' . str_random(8))),
            'url_video' => $request->url_video,
            'duration'  => $duration->format('%H:%I:%S'),
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

        $lesson->parts = $lesson->parts - 1;
        $lesson->save();

        return $this->resJsonSuccess('Kurikulum berhasil dihapus.', 200);
    }
}
