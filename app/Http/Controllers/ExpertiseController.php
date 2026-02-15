<?php

namespace App\Http\Controllers;

use App\Models\Expertise;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpertiseController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            $user = $request->user();
            abort_unless($user && method_exists($user, 'isAdmin') && $user->isAdmin(), 403);
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = Expertise::query()->orderBy('name');

        $searchField = strval($request->input('search_field', ''));
        $searchQuery = trim(strval($request->input('search_query', '')));

        if ($searchQuery !== '') {
            if (strtolower($searchField) === 'id') {
                $query->where('id', 'like', "%{$searchQuery}%");
            } else {
                $query->where('name', 'like', "%{$searchQuery}%");
            }
        }

        $expertises = $query->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($expertises);
        }

        return Inertia::render('Admin/Expertise/Index', [
            'expertises' => $expertises,
            'filters' => [
                'search_field' => $searchField ?: 'name',
                'search_query' => $searchQuery,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:expertises,name'],
        ]);

        $expertise = Expertise::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'expertise' => $expertise,
            ], 201);
        }

        return redirect()->route('expertises.index');
    }

    public function update(Request $request, Expertise $expertise)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:expertises,name,' . $expertise->id],
        ]);

        $expertise->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'expertise' => $expertise->fresh(),
            ]);
        }

        return redirect()->route('expertises.index');
    }

    public function destroy(Request $request, Expertise $expertise)
    {
        $expertise->delete();

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect()->route('expertises.index');
    }
}
