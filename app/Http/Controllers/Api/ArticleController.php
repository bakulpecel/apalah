<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Category;
use App\Transformers\ArticleTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class ArticleController extends Controller
{
    public function guestIndex(Request $request)
    {
        if ($request->hasHeader('paginator')) {
            if (Auth::user()->role_id === 1 || Auth::user()->role_id === 3) {
                $paginator = Article::where('status', 1)
                    ->orderBy('published_at', 'desc')
                    ->paginate($request->header('paginator'));
            } else {
                $paginator = Article::where('user_id', Auth::user()->id)
                    ->where('status', 1)
                    ->orderBy('published_at', 'desc')
                    ->paginate($request->header('paginator'));
            }

            $articles = $paginator->getCollection();

            $response = fractal()
                ->collection($articles, new ArticleTransformer)
                ->paginateWith(new IlluminatePaginatorAdapter($paginator))
                ->toArray();

            return response()
                ->json($response, 200);
        }

        if (Auth::user()->role_id === 1 || Auth::user()->role_id === 3) {
            $articles = Article::where('status', 1)
                ->orderBy('published_at', 'desc')
                ->get();
        } else {
            $articles = Article::where('user_id', Auth::user()->id)
                ->where('status', 1)
                ->orderBy('published_at', 'desc')
                ->get();
        }

        $response = fractal()
            ->collection($articles)
            ->transformWith(new ArticleTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function guestIndexDraft(Request $request)
    {
        if ($request->hasHeader('paginator')) {
            if (Auth::user()->role_id === 1 || Auth::user()->role_id === 3) {
                $paginator = Article::where('status', 0)
                    ->orderBy('published_at', 'desc')
                    ->paginate($request->header('paginator'));
            } else {
                $paginator = Article::where('user_id', Auth::user()->id)
                    ->where('status', 0)
                    ->orderBy('published_at', 'desc')
                    ->paginate($request->header('paginator'));
            }

            $articles = $paginator->getCollection();

            $response = fractal()
                ->collection($articles, new ArticleTransformer)
                ->paginateWith(new IlluminatePaginatorAdapter($paginator))
                ->toArray();

            return response()
                ->json($response, 200);
        }

        if (Auth::user()->role_id === 1 || Auth::user()->role_id === 3) {
            $articles = Article::where('status', 0)
                ->orderBy('published_at', 'desc')
                ->get();
        } else {
            $articles = Article::where('user_id', Auth::user()->id)
                ->where('status', 0)
                ->orderBy('published_at', 'desc')
                ->get();
        }

        $response = fractal()
            ->collection($articles)
            ->transformWith(new ArticleTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function authIndex(Request $request)
    {
        if ($request->hasHeader('paginator')) {
            $paginator = Article::where('status', 1)
                ->orderBy('published_at', 'desc')
                ->paginate($request->header('paginator'));
            $articles = $paginator->getCollection();

            $response = fractal()
                ->collection($articles, new ArticleTransformer)
                ->paginateWith(new IlluminatePaginatorAdapter($paginator))
                ->toArray();

            return response()
                ->json($response, 200);
        }

        $articles = Article::where('status', 1)
            ->orderBy('published_at', 'desc')
            ->get();

        $response = fractal()
            ->collection($articles, new ArticleTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function show($slug)
    {
        $article = Article::where('slug', $slug)
            ->where('status', 1)
            ->first();

        if (!$article) {
            return $this->resJsonError('Artikel tidak ditemukan', 404);
        }

        $response = fractal()
            ->item($article)
            ->transformWith(new ArticleTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'      => 'required|min:5|max:255',
            'slug'       => 'required|min:5|max:255|alpha_dash|unique:articles',
            'content'    => 'required|min:150',
            'thumbnail'  => 'image|mimes:jpeg,jpg,png|max:512',
            'status'     => 'required|integer|between:0,1',
            'categories' => 'required|array',
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
                ->put('thumbnail/articles/' . $imageName = time() . '.' . $request->thumbnail->getClientOriginalExtension(),
                    File::get($request->file('thumbnail'))
                );
        }

        if ($request->status == 1) {
            $published = Carbon::now('Asia/Jakarta')->toDateTimeString();
        }
        
        $article = Article::create([
            'title'        => $request->title,
            'slug'         => $request->slug,
            'content'      => $request->content,
            'thumbnail'    => $imageName ?? null,
            'status'       => $request->status,
            'user_id'      => Auth::user()->id,
            'published_at' => $published ?? null,
        ]);

        foreach ($request->categories as $keyCategory => $valueCategory) {
            $category = Category::firstOrCreate([
                'slug'     => str_replace(' ', '-', strtolower($valueCategory)),
                'category' => $valueCategory,
            ]);

            $articleCategory = ArticleCategory::create([
                'article_id'  => $article->id,
                'category_id' => $category->id,
            ]);
        }

        $response = fractal()
            ->item($article)
            ->transformWith(new ArticleTransformer)
            ->toArray();

        return response()
            ->json($response, 201);
    }

    public function update(Request $request, $slug)
    {
        $article = Article::where('slug', $slug)
            ->first();

        if (!$article) {
            return $this->resJsonError('Artikel tidak ditemukan', 404);
        }

        $this->authorize('update', $article);

        $validator = Validator::make($request->all(), [
            'title'      => 'required|min:5|max:255',
            'slug'       => 'required|min:5|max:255|alpha_dash',
            'content'    => 'required|min:150',
            'thumbnail'  => 'image|mimes:jpeg,jpg,png|max:512',
            'status'     => 'required|integer|between:0,1',
            'categories' => 'required|array',
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
                ->put('thumbnail/articles/' . $imageName = time() . '.' . $request->thumbnail->getClientOriginalExtension(),
                    File::get($request->file('thumbnail'))
                );
        }

        if ($request->status == 1) {
            $published = Carbon::now('Asia/Jakarta')
                ->toDateTimeString();
        }

        $article->update([
            'title'        => $request->title,
            'slug'         => $request->slug,
            'content'      => $request->content,
            'thumbnail'    => $imageName ?? null,
            'status'       => $request->status,
            'published_at' => $published ?? null,
        ]);

        ArticleCategory::where('article_id', $article->id)
            ->delete();

        foreach ($request->categories as $keyCategory => $valueCategory) {
            $category = Category::firstOrCreate([
                'slug'     => str_replace(' ', '-', strtolower($valueCategory)),
                'category' => $valueCategory,
            ]);

            $articleCategory = ArticleCategory::create([
                'article_id'  => $article->id,
                'category_id' => $category->id,
            ]);
        }

        $response = fractal()
            ->item($article)
            ->transformWith(new ArticleTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function destroy($slug)
    {
        $article = Article::where('slug', $slug)
            ->first();

        if (!$article) {
            return $this->resJsonError('Artikel tidak ditemukan', 404);
        }

        $this->authorize('delete', $article);

        $article->delete();

        return $this->resJsonSuccess('Artikel berhasil dihapus.', 200);
    }
}
