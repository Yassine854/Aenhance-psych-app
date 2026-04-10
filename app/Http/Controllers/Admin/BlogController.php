<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogRequest;
use App\Models\Blog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            $user = $request->user();
            abort_unless($user && method_exists($user, 'isAdmin') && $user->isAdmin(), 403);

            return $next($request);
        });
    }

    public function index(Request $request): Response
    {
        $searchField = strtolower(trim((string) $request->input('search_field', 'title')));
        $searchQuery = trim((string) $request->input('search_query', ''));

        if (! in_array($searchField, ['id', 'title', 'author', 'category'], true)) {
            $searchField = 'title';
        }

        $query = Blog::query()
            ->with('author:id,name,email')
            ->orderByRaw('CASE WHEN published_at IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        if ($searchQuery !== '') {
            switch ($searchField) {
                case 'id':
                    $query->where('id', 'like', "%{$searchQuery}%");
                    break;
                case 'author':
                    $query->whereHas('author', function ($authorQuery) use ($searchQuery) {
                        $authorQuery->where('name', 'like', '%'.$searchQuery.'%')
                            ->orWhere('email', 'like', '%'.$searchQuery.'%');
                    });
                    break;
                case 'category':
                    $query->where('category', 'like', '%'.$searchQuery.'%');
                    break;
                case 'title':
                default:
                    $query->where(function ($blogQuery) use ($searchQuery) {
                        $blogQuery->where('title', 'like', '%'.$searchQuery.'%')
                            ->orWhere('slug', 'like', '%'.$searchQuery.'%')
                            ->orWhere('excerpt', 'like', '%'.$searchQuery.'%');
                    });
                    break;
            }
        }

        $blogs = $query
            ->paginate(12)
            ->through(fn (Blog $blog) => $this->presentBlog($blog, false))
            ->withQueryString();

        return Inertia::render('Admin/Blogs/Index', [
            'blogs' => $blogs,
            'status' => session('status'),
            'error' => session('error'),
            'filters' => [
                'search_field' => $searchField,
                'search_query' => $searchQuery,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Blogs/Create', [
            'status' => session('status'),
        ]);
    }

    public function store(BlogRequest $request): RedirectResponse|JsonResponse
    {
        $data = $this->validatedPayload($request);
        $data['author_id'] = $request->user()->id;
        $data['slug'] = $this->makeUniqueSlug(($data['slug'] ?? '') ?: $data['title']);
        $data['published_at'] = $this->resolvePublishedAt($data['published_at'] ?? null);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->storeFeaturedImage($request->file('featured_image'));
        }

        $blog = Blog::create($data);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'blog' => $this->presentBlog($blog->load('author'), true),
            ], 201);
        }

        return redirect()
            ->route('admin.blogs.index')
            ->with('status', 'Blog created successfully.');
    }

    public function show(Blog $blog): Response
    {
        $blog->load('author:id,name,email');

        return Inertia::render('Admin/Blogs/Show', [
            'blog' => $this->presentBlog($blog, true),
            'status' => session('status'),
        ]);
    }

    public function edit(Blog $blog): Response
    {
        $blog->load('author:id,name,email');

        return Inertia::render('Admin/Blogs/Edit', [
            'blog' => $this->presentBlog($blog, true),
            'status' => session('status'),
        ]);
    }

    public function update(BlogRequest $request, Blog $blog): RedirectResponse|JsonResponse
    {
        $data = $this->validatedPayload($request);
        $data['slug'] = $this->makeUniqueSlug(($data['slug'] ?? '') ?: $data['title'], $blog->id);
        $data['published_at'] = $this->resolvePublishedAt($data['published_at'] ?? null, $blog);

        $removeFeaturedImage = $request->boolean('remove_featured_image');

        if ($request->hasFile('featured_image')) {
            $this->deleteFeaturedImage($blog->featured_image);
            $data['featured_image'] = $this->storeFeaturedImage($request->file('featured_image'));
        } elseif ($removeFeaturedImage) {
            $this->deleteFeaturedImage($blog->featured_image);
            $data['featured_image'] = null;
        }

        $blog->update($data);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'blog' => $this->presentBlog($blog->fresh()->load('author'), true),
            ]);
        }

        return redirect()
            ->route('admin.blogs.index')
            ->with('status', 'Blog updated successfully.');
    }

    public function destroy(Request $request, Blog $blog): RedirectResponse|JsonResponse
    {
        $blog->delete();

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([], 204);
        }

        return redirect()
            ->route('admin.blogs.index')
            ->with('status', 'Blog deleted successfully.');
    }

    private function validatedPayload(BlogRequest $request): array
    {
        $data = $request->validated();

        unset($data['featured_image'], $data['remove_featured_image']);

        return $data;
    }

    private function presentBlog(Blog $blog, bool $includeFullContent = false): array
    {
        $content = (string) ($blog->content ?? '');
        $plainText = trim(strip_tags($content));

        return [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'excerpt' => $blog->excerpt,
            'content' => $includeFullContent ? $content : Str::limit($plainText, 220),
            'featured_image' => $blog->featured_image,
            'published_at' => optional($blog->published_at)?->format('Y-m-d'),
            'category' => $blog->category,
            'views' => (int) $blog->views,
            'created_at' => optional($blog->created_at)->toISOString(),
            'updated_at' => optional($blog->updated_at)->toISOString(),
            'author' => $blog->author ? [
                'id' => $blog->author->id,
                'name' => $blog->author->name,
                'email' => $blog->author->email,
            ] : null,
        ];
    }

    private function makeUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($source);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'blog';
        $slug = $baseSlug;
        $counter = 2;

        while (
            Blog::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function resolvePublishedAt(mixed $publishedAt, ?Blog $existingBlog = null): ?Carbon
    {
        if (! empty($publishedAt)) {
            return Carbon::parse($publishedAt);
        }

        return $existingBlog?->published_at ? Carbon::parse($existingBlog->published_at) : null;
    }

    private function storeFeaturedImage(UploadedFile $file): string
    {
        return $file->store('blogs/featured-images', config('app.avatar_disk', 'public'));
    }

    private function deleteFeaturedImage(?string $path): void
    {
        if (! $path || preg_match('#^https?://#i', $path)) {
            return;
        }

        $disk = config('app.avatar_disk', 'public');
        $normalizedPath = Str::startsWith($path, '/storage/') ? Str::after($path, '/storage/') : ltrim($path, '/');

        if (Storage::disk($disk)->exists($normalizedPath)) {
            Storage::disk($disk)->delete($normalizedPath);
        }
    }
}