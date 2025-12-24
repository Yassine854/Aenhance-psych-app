<?php

namespace App\Http\Controllers;

use App\Http\Requests\PsychologistProfileRequest;
use App\Models\PsychologistProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PsychologistProfileController extends Controller
{
    public function index(Request $request): Response
    {
        $profiles = PsychologistProfile::with('user')->paginate(15);

        return Inertia::render('Admin/Psychologist/Index', [
            'profiles' => $profiles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Psychologist/Create');
    }

    public function store(PsychologistProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['user_id'])) {
            $data['user_id'] = $request->user()->id;
        }

        $profile = PsychologistProfile::create($data);

        return redirect()->route('psychologist-profiles.edit', $profile);
    }

    public function show(PsychologistProfile $psychologistProfile): Response
    {
        return Inertia::render('Admin/Psychologist/Show', [
            'profile' => $psychologistProfile->load('user'),
        ]);
    }

    public function edit(PsychologistProfile $psychologistProfile): Response
    {
        return Inertia::render('Admin/Psychologist/Edit', [
            'profile' => $psychologistProfile->load('user'),
        ]);
    }

    public function update(PsychologistProfileRequest $request, PsychologistProfile $psychologistProfile): RedirectResponse
    {
        $psychologistProfile->update($request->validated());

        return redirect()->route('psychologist-profiles.edit', $psychologistProfile);
    }

    public function destroy(PsychologistProfile $psychologistProfile): RedirectResponse
    {
        $psychologistProfile->delete();

        return redirect()->route('psychologist-profiles.index');
    }
}
