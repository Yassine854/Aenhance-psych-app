<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RessourceRequest;
use App\Models\Ressource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RessourceController extends Controller
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

        if (! in_array($searchField, ['id', 'title', 'author'], true)) {
            $searchField = 'title';
        }

        $query = Ressource::query()
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
                case 'title':
                default:
                    $query->where(function ($ressourceQuery) use ($searchQuery) {
                        $ressourceQuery->where('title', 'like', '%'.$searchQuery.'%')
                            ->orWhere('slug', 'like', '%'.$searchQuery.'%')
                            ->orWhere('description', 'like', '%'.$searchQuery.'%');
                    });
                    break;
            }
        }

        $ressources = $query
            ->paginate(12)
            ->through(fn (Ressource $ressource) => $this->presentRessource($ressource))
            ->withQueryString();

        return Inertia::render('Admin/Ressources/Index', [
            'ressources' => $ressources,
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
        return Inertia::render('Admin/Ressources/Create', [
            'status' => session('status'),
        ]);
    }

    public function store(RessourceRequest $request): RedirectResponse|JsonResponse
    {
        $data = $this->validatedPayload($request);
        $data['author_id'] = $request->user()->id;
        $data['slug'] = $this->makeUniqueSlug(($data['slug'] ?? '') ?: $data['title']);
        $data['published_at'] = $this->resolvePublishedAt($data['published_at'] ?? null);

        if ($request->hasFile('pdf')) {
            $data['pdf'] = $this->storePdf($request->file('pdf'));
        }

        $ressource = Ressource::create($data);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'ressource' => $this->presentRessource($ressource->load('author')),
            ], 201);
        }

        return redirect()
            ->route('admin.ressources.index')
            ->with('status', 'Ressource created successfully.');
    }

    public function show(Ressource $ressource): Response
    {
        $ressource->load('author:id,name,email');

        return Inertia::render('Admin/Ressources/Show', [
            'ressource' => $this->presentRessource($ressource),
            'status' => session('status'),
        ]);
    }

    public function edit(Ressource $ressource): Response
    {
        $ressource->load('author:id,name,email');

        return Inertia::render('Admin/Ressources/Edit', [
            'ressource' => $this->presentRessource($ressource),
            'status' => session('status'),
        ]);
    }

    public function update(RessourceRequest $request, Ressource $ressource): RedirectResponse|JsonResponse
    {
        $data = $this->validatedPayload($request);
        $data['slug'] = $this->makeUniqueSlug(($data['slug'] ?? '') ?: $data['title'], $ressource->id);
        $data['published_at'] = $this->resolvePublishedAt($data['published_at'] ?? null, $ressource);

        $removePdf = $request->boolean('remove_pdf');

        if ($request->hasFile('pdf')) {
            $this->deletePdf($ressource->pdf);
            $data['pdf'] = $this->storePdf($request->file('pdf'));
        } elseif ($removePdf) {
            $this->deletePdf($ressource->pdf);
            $data['pdf'] = null;
        }

        $ressource->update($data);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'ressource' => $this->presentRessource($ressource->fresh()->load('author')),
            ]);
        }

        return redirect()
            ->route('admin.ressources.index')
            ->with('status', 'Ressource updated successfully.');
    }

    public function destroy(Request $request, Ressource $ressource): RedirectResponse|JsonResponse
    {
        $this->deletePdf($ressource->pdf);
        $ressource->delete();

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([], 204);
        }

        return redirect()
            ->route('admin.ressources.index')
            ->with('status', 'Ressource deleted successfully.');
    }

    private function validatedPayload(RessourceRequest $request): array
    {
        $data = $request->validated();

        unset($data['pdf'], $data['remove_pdf']);

        return $data;
    }

    private function presentRessource(Ressource $ressource): array
    {
        return [
            'id' => $ressource->id,
            'title' => $ressource->title,
            'slug' => $ressource->slug,
            'description' => $ressource->description,
            'pdf' => $ressource->pdf,
            'published_at' => optional($ressource->published_at)?->format('Y-m-d'),
            'created_at' => optional($ressource->created_at)->toISOString(),
            'updated_at' => optional($ressource->updated_at)->toISOString(),
            'author' => $ressource->author ? [
                'id' => $ressource->author->id,
                'name' => $ressource->author->name,
                'email' => $ressource->author->email,
            ] : null,
        ];
    }

    private function makeUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($source);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'ressource';
        $slug = $baseSlug;
        $counter = 2;

        while (
            Ressource::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function resolvePublishedAt(mixed $publishedAt, ?Ressource $existingRessource = null): ?Carbon
    {
        if (! empty($publishedAt)) {
            return Carbon::parse($publishedAt);
        }

        return $existingRessource?->published_at ? Carbon::parse($existingRessource->published_at) : null;
    }

    private function storePdf(UploadedFile $file): string
    {
        return $file->store('ressources/pdfs', 'public');
    }

    private function deletePdf(?string $path): void
    {
        if (! $path || preg_match('#^https?://#i', $path)) {
            return;
        }

        $normalizedPath = Str::startsWith($path, '/storage/') ? Str::after($path, '/storage/') : ltrim($path, '/');

        if (Storage::disk('public')->exists($normalizedPath)) {
            Storage::disk('public')->delete($normalizedPath);
        }
    }
}