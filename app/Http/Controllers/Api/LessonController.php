<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Lesson;
use App\Models\LessonCategory;
use App\Models\LessonPart;
use App\Models\PremiumUser;
use App\Transformers\LessonTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class LessonController extends Controller
{
    public function guestIndex(Request $request)
    {
        if ($request->hasHeader('paginator')) {
            if (Auth::user()->role_id === 1) {
                $paginator = Lesson::where('status', 1)
                    ->orderBy('published_at', 'desc')
                    ->paginate($request->header('paginator'));

            } else {
                $paginator = Lesson::where('user_id', Auth::user()->id)
                    ->where('status', 1)
                    ->orderBy('published_at', 'desc')
                    ->paginate($request->header('paginator'));
            }

            $lessons = $paginator->getCollection();

            $response = fractal()
                ->collection($lessons, new LessonTransformer)
                ->paginateWith(new IlluminatePaginatorAdapter($paginator))
                ->toArray();

            return response()
                ->json($response, 200);
        }

        if (Auth::user()->role_id === 1) {
            $lessons = Lesson::where('status', 1)
                ->orderBy('published_at', 'desc')
                ->get();
        } else {
            $lessons = Lesson::where('user_id', Auth::user()->id)
                ->where('status', 1)
                ->orderBy('published_at', 'desc')
                ->get();
        }

        $response = fractal()
            ->collection($lessons)
            ->transformWith(new LessonTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function authIndex(Request $request)
    {
        if ($request->hasHeader('paginator')) {
            $paginator = Lesson::where('status', 1)
                ->orderBy('published_at', 'desc')
                ->paginate($request->header('paginator'));
            $lessons   = $paginator->getCollection();

            $response = fractal()
                ->collection($lessons, new LessonTransformer)
                ->paginateWith(new IlluminatePaginatorAdapter($paginator))
                ->toArray();

            return response()
                ->json($response, 200);
        }

        $lessons = Lesson::where('status', 1)
            ->orderBy('published_at', 'desc')
            ->get();

        $response = fractal()
            ->collection($lessons, new LessonTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function guestShow($slug)
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
                $response = fractal()
                    ->item($lesson)
                    ->transformWith(new LessonTransformer)
                    ->toArray();

                return response()
                    ->json($response, 200);            
            } else {
                return $this->resJsonError('Anda harus menjadi premium member!', 403);
            }
        }

        $response = fractal()
            ->item($lesson)
            ->transformWith(new LessonTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function authShow($slug)
    {
        $lesson = Lesson::where('slug', $slug)
            ->where('status', 1)
            ->first();

        if (!$lesson) {
            return $this->resJsonError('Pelajaran tidak ditemukan', 404);
        }

        if ($lesson->type === 1) {
            return $this->resJsonError('Anda harus menjadi premium member!', 403);
        }

        $response = fractal()
            ->item($lesson)
            ->transformWith(new LessonTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'           => 'required|min:5|',
            'slug'            => 'required|min:5|alpha_dash|unique:lessons',
            'summary'         => 'required|min:20|',
            'thumbnail'       => 'image|mimes:jpeg,jpg,png|max:512',
            'url_source_code' => 'active_url',
            'type'            => 'required|integer|between:0,1',
            'status'          => 'required|integer|between:0,1',
            'categories'      => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code'    => 400,
                    'message' => $validator->errors(),
                ],
            ], 400);
        }

        if ($request->hasFile('thumbnail')) {
            Storage::disk('local')
                ->put('thumbnail/lessons/' . $imageName = time() . '.' . $request->thumbnail->getClientOriginalExtension(),
                    File::get($request->file('thumbnail'))
                );
        }

        if ($request->status == 1) {
            $published = Carbon::now('Asia/Jakarta')->toDateTimeString();
        }

        $lesson =  Lesson::create([
            'title'           => $request->title,
            'slug'            => $request->slug,
            'summary'         => $request->summary,
            'thumbnail'       => $imageName ?? null,
            'url_source_code' => $request->url_source_code ?? null,
            'type'            => $request->type,
            'status'          => $request->status,
            'user_id'         => Auth::user()->id,
            'published_at'    => $published ?? null,
        ]);

        foreach ($request->categories as $keyCategory => $valueCategory) {
            $category = Category::firstOrCreate([
                'slug'     => str_replace(' ', '-', strtolower($valueCategory)),
                'category' => $valueCategory,
            ]);

            $lessonCategory = LessonCategory::create([
                'lesson_id'   => $lesson->id,
                'category_id' => $category->id,
            ]);
        }

        $response = fractal()
            ->item($lesson)
            ->transformWith(new LessonTransformer)
            ->toArray();

        return response()
            ->json($response, 201);
    }

    public function update(Request $request, $slug)
    {
        $lesson = Lesson::where('slug', $slug)
            ->first();

        if (!$lesson) {
            return $this->resJsonError('Pelajaran tidak ditemukan', 404);
        }

        $this->authorize('update', $lesson);

        $validator = Validator::make($request->all(), [
            'title'           => 'required|min:5|',
            'slug'            => 'required|min:5|alpha_dash',
            'summary'         => 'required|min:20|',
            'thumbnail'       => 'image|mimes:jpeg,jpg,png|max:512',
            'url_source_code' => 'active_url',
            'type'            => 'required|integer|between:0,1',
            'status'          => 'required|integer|between:0,1',
            'categories'      => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code'    => 400,
                    'message' => $validator->errors(),
                ],
            ], 400);
        }

        if ($request->hasFile('thumbnail')) {
            Storage::disk('local')
                ->put('thumbnail/lessons/' . $imageName = time() . '.' . $request->thumbnail->getClientOriginalExtension(),
                    File::get($request->file('thumbnail'))
                );
        }

        if ($request->status == 1) {
            $published = Carbon::now('Asia/Jakarta')
                ->toDateTimeString();
        }

        $lesson->update([
            'title'           => $request->title,
            'slug'            => $request->slug,
            'summary'         => $request->summary,
            'parts'           => LessonPart::where('lesson_id', $lesson->id)->count(),
            'thumbnail'       => $imageName ?? null,
            'url_source_code' => $request->url_source_code ?? null,
            'type'            => $request->type,
            'status'          => $request->status,
            'published_at'    => $published ?? null,
        ]);

        LessonCategory::where('lesson_id', $lesson->id)
            ->delete();

        foreach ($request->categories as $keyCategory => $valueCategory) {
            $category = Category::firstOrCreate([
                'slug'     => str_replace(' ', '-', strtolower($valueCategory)),
                'category' => $valueCategory,
            ]);

            $lessonCategory = LessonCategory::create([
                'lesson_id'   => $lesson->id,
                'category_id' => $category->id,
            ]);
        }

        $response = fractal()
            ->item($lesson)
            ->transformWith(new LessonTransformer)
            ->toArray();

        return response()
            ->json($response, 201);
    }

    public function destroy($slug)
    {
        $lesson = Lesson::where('slug', $slug)
            ->first();

        if (!$lesson) {
            return $this->resJsonError('Pelajaran tidak ditemukan', 404);
        }

        $this->authorize('delete', $lesson);

        $lesson->delete();

        return $this->resJsonSuccess('Pelajaran berhasil dihapus.', 200);
    }
}
