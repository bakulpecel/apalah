<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    public function show($image)
    {
        $thumbnailLesson = storage_path('app/thumbnail/lessons/') . $image;
        $thumbnailArticle = storage_path('app/thumbnail/articles/') . $image;
        $avatar = storage_path('app/avatar/') . $image;

        if (!File::exists($thumbnailLesson) || !File::exists($thumbnailArticle)) {
            $default = storage_path('app/') . 'default.jpg';
            $image = File::get($default);
            $type  = File::mimeType($default);
        }

        if (File::exists($thumbnailLesson)) {
            $image = File::get($thumbnailLesson);
            $type = File::mimeType($thumbnailLesson);
        }

        if (File::exists($thumbnailArticle)) {
            $image = File::get($thumbnailArticle);
            $type = File::mimeType($thumbnailArticle);
        }

        if (File::exists($avatar)) {
            $image = File::get($avatar);
            $type = File::mimeType($avatar);
        }

        return response()
            ->make($image, 200)
            ->header("Content-Type", $type);
    }
}
