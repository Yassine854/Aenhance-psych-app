<?php

namespace App\Http\Controllers;

use App\Models\Specialisation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SpecialisationController extends Controller
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
        $query = Specialisation::query()->orderBy('name');

        $searchField = strval($request->input('search_field', ''));
        $searchQuery = trim(strval($request->input('search_query', '')));

        if ($searchQuery !== '') {
            if (strtolower($searchField) === 'id') {
                $query->where('id', 'like', "%{$searchQuery}%");
            } else {
                $query->where('name', 'like', "%{$searchQuery}%");
            }
        }

        $specialisations = $query->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($specialisations);
        }

        return Inertia::render('Admin/Specialisation/Index', [
            'specialisations' => $specialisations,
            'filters' => [
                'search_field' => $searchField ?: 'name',
                'search_query' => $searchQuery,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:specialisations,name'],
        ]);

        $specialisation = Specialisation::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'specialisation' => $specialisation,
            ], 201);
        }

        return redirect()->route('specialisations.index');
    }

    public function update(Request $request, Specialisation $specialisation)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:specialisations,name,' . $specialisation->id],
        ]);

        $specialisation->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'specialisation' => $specialisation->fresh(),
            ]);
        }

        return redirect()->route('specialisations.index');
    }

    public function destroy(Request $request, Specialisation $specialisation)
    {
        $specialisation->delete();

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect()->route('specialisations.index');
    }
}
