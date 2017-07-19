<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Lesson;
use App\Models\LessonCategory;
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
    public function index(Request $request)
    {
        if ($request->hasHeader('paginator')) {
            $paginator = Lesson::paginate($request->paginator);
            $lessons   = $paginator->getCollection();

            $response = fractal()
                ->collection($lessons, new LessonTransformer)
                ->paginateWith(new IlluminatePaginatorAdapter($paginator))
                ->toArray();

            return response()
                ->json($response, 200);
        }

        $lessons = Lesson::all();

        $response = fractal()
            ->collection($lessons, new LessonTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function indexPublish(Request $request)
    {
        if ($request->hasHeader('paginator')) {
            $paginator = Lesson::where('status', 1)->paginate($request->paginator);
            $lessons   = $paginator->getCollection();

            $response = fractal()
                ->collection($lessons, new LessonTransformer)
                ->paginateWith(new IlluminatePaginatorAdapter($paginator))
                ->toArray();

            return response()
                ->json($response, 200);
        }

        $lessons = Lesson::where('status', 1)->get();

        $response = fractal()
            ->collection($lessons, new LessonTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function show(Request $request, $slug)
    {
        $lesson = Lesson::where('slug', $slug)
            ->first();

        if (!$lesson) {
            return $this->resJsonError('Pelajaran tidak ditemukan', 404);
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
            $published = Carbon::now('Asia/Jakarta');
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

    public function destroy($slug)
    {
        $lesson = Lesson::where('slug', $slug)
            ->first();

        if (!$lesson) {
            return $this->resJsonError('Artikel tidak ditemukan', 404);
        }

        $this->authorize('delete', $lesson);

        $lesson->delete();

        return $this->resJsonSuccess('Artikel berhasil dihapus.', 200);
    }
}
