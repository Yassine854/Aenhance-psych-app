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
        $specialisations = Specialisation::query()
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($specialisations);
        }

        return Inertia::render('Admin/Specialisation/Index', [
            'specialisations' => $specialisations,
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
