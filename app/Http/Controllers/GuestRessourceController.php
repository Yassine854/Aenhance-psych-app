<?php

namespace App\Http\Controllers;

use App\Models\Ressource;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class GuestRessourceController extends Controller
{
    public function index(): Response
    {
        $ressources = Ressource::query()
            ->with('author:id,name,email')
            ->orderByRaw('CASE WHEN published_at IS NULL THEN created_at ELSE published_at END DESC')
            ->orderByDesc('created_at')
            ->paginate(5)
            ->through(function (Ressource $ressource) {
                return [
                    'id' => $ressource->id,
                    'title' => $ressource->title,
                    'slug' => $ressource->slug,
                    'description' => $ressource->description,
                    'pdf' => $ressource->pdf,
                    'published_at' => optional($ressource->published_at)?->toISOString(),
                    'created_at' => optional($ressource->created_at)->toISOString(),
                    'author' => $ressource->author ? [
                        'name' => $ressource->author->name,
                        'email' => $ressource->author->email,
                    ] : null,
                ];
            })
            ->withQueryString();

        return Inertia::render('guest/ressource/Index', [
            'ressources' => $ressources,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'authUser' => auth()->user(),
        ]);
    }
}