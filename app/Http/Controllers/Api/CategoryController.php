<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Category;
use App\Models\Lesson;
use App\Models\LessonCategory;
use App\Transformers\ArticleTransformer;
use App\Transformers\ArticleCategoryTransformer;
use App\Transformers\CategoryTransformer;
use App\Transformers\LessonTransformer;
use App\Transformers\LessonCategoryTransformer;
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
                ->transformWith(new LessonCategoryTransformer)
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

    public function filterArticle(Request $request)
    {
        if (!$request->has('category')) {
            $articleCategory = ArticleCategory::distinct()
                ->get();

            $categories = [];

            foreach ($articleCategory as $keyAC => $valueAC) {
                $categories[] = Category::find($valueAC->category_id);
            }

            if (!$categories) {
                return $this->resJsonError('Tidak ditemukan kategori untuk artikel!.', 404);
            }

            $response = fractal()
                ->collection($categories)
                ->transformWith(new CategoryTransformer)
                ->toArray();

            return response()
                ->json($response, 200);
        }

        $category = Category::where('slug', $request->category)
            ->first();

        if (!$category) {
            return $this->resJsonError('Tidak ditemukan kategori!.', 404);
        }

        $articleCategory = ArticleCategory::where('category_id', $category->id)
            ->get();

        if (!$articleCategory) {
            return $this->resJsonError('Tidak ditemukan artikel dengan kategori '. $category->category, 404);
        }

        $articles = [];

        foreach ($articleCategory as $valueAC) {
            $articles[] = Article::where('status', 1)
                ->find($valueAC->article_id);
        }

        if (!$articles) {
            return $this->resJsonError('Tidak ditemukan artikel dengan kategori '. $category->category, 404);
        }

        $response = fractal()
            ->collection($articles)
            ->transformWith(new ArticleTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function filterLesson(Request $request)
    {
        if (!$request->has('category')) {
            $lessonCategory = LessonCategory::distinct()
                ->get();

            $categories = [];

            foreach ($lessonCategory as $keyLC => $valueLC) {
                $categories[] = Category::find($valueLC->category_id);
            }

            if (!$categories) {
                return $this->resJsonError('Tidak ditemukan kategori untuk pelajaran!.', 404);
            }

            $response = fractal()
                ->collection($categories)
                ->transformWith(new CategoryTransformer)
                ->toArray();

            return response()
                ->json($response, 200);
        }

        $category = Category::where('slug', $request->category)
            ->first();

        if (!$category) {
            return $this->resJsonError('Tidak ditemukan kategory!.', 404);
        }

        $lessonCategory = LessonCategory::where('category_id', $category->id)
            ->get();

        if (!$lessonCategory) {
            return $this->resJsonError('Tidak ditemukan pelajaran dengan kategori '. $category->category, 404);
        }

        $lessons = [];

        foreach ($lessonCategory as $valueLC) {
            $lessons[] = Lesson::where('status', 1)
                ->find($valueLC->lesson_id);
        }

        if (!$lessons) {
            return $this->resJsonError('Tidak ditemukan pelajaran dengan kategori '. $category->category, 404);
        }

        $response = fractal()
            ->collection($lessons)
            ->transformWith(new LessonTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }
}
