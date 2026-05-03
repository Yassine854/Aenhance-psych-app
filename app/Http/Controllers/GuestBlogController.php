<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GuestBlogController extends Controller
{
    public function index(): Response
    {
        $blogs = Blog::query()
            ->with('author:id,name,email')
            ->orderByRaw('CASE WHEN published_at IS NULL THEN created_at ELSE published_at END DESC')
            ->orderByDesc('created_at')
            ->paginate(5)
            ->through(function (Blog $blog) {
                $content = (string) ($blog->content ?? '');
                $plainText = trim(strip_tags($content));

                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'slug' => $blog->slug,
                    'excerpt' => $blog->excerpt,
                    'content' => $content,
                    'content_preview' => Str::limit($plainText, 240),
                    'featured_image' => $blog->featured_image,
                    'published_at' => optional($blog->published_at)?->toISOString(),
                    'category' => $blog->category,
                    'views' => (int) $blog->views,
                    'author' => $blog->author ? [
                        'name' => $blog->author->name,
                        'email' => $blog->author->email,
                    ] : null,
                ];
            })
            ->withQueryString();

        return Inertia::render('guest/blog/Index', [
            'blogs' => $blogs,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'authUser' => auth()->user(),
        ]);
    }
}