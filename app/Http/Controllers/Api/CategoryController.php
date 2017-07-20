<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Category;
use App\Models\Lesson;
use App\Models\LessonCategory;
use App\Transformers\ArticleCategoryTransformer;
use App\Transformers\CategoryTransformer;
use App\Transformers\LessonCategoryTransfomer;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('lesson')) {
            $lesson = Lesson::where('slug', $request->lesson)
                ->first();

            if (!$lesson) {
                return $this->resJsonError('Tidak ditemukan kategori pelajaran', 404);
            }

            $lessonCategory = LessonCategory::where('lesson_id', $lesson->id)
                ->get();

            $response = fractal()
                ->collection($lessonCategory)
                ->transformWith(new LessonCategoryTransfomer)
                ->toArray();

            return response()
                ->json($response, 200);
        }

        if ($request->has('article')) {
            $article = Article::where('slug', $request->article)
                ->first();

            if (!$article) {
                return $this->resJsonError('Tidak ditemukan kategori artikel', 404);
            }

            $articleCategory = ArticleCategory::where('article_id', $article->id)
                ->get();

            $response = fractal()
                ->collection($articleCategory)
                ->transformWith(new ArticleCategoryTransformer)
                ->toArray();

            return response()
                ->json($response, 200);
        }

        $categories = Category::all();

        $response = fractal()
            ->collection($categories)
            ->transformWith(new CategoryTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }
}
