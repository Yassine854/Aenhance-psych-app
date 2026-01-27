<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppFeeRequest;
use App\Models\AppFee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request as HttpRequest;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AppFeeController extends Controller
{
    /** Display the app fee page (single editable percentage). */
    public function index(): InertiaResponse
    {
        $fee = AppFee::first();
        return Inertia::render('Admin/AppFee/Index', [
            'appFee' => $fee,
        ]);
    }

    /** Store or update the app fee (ensures single record). */
    public function store(AppFeeRequest $request): Response|JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        AppFee::updateOrCreate([], ['percentage' => $data['percentage']]);

        $fee = AppFee::first();
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'appFee' => $fee], 200);
        }

        return redirect()->route('app-fees.index')->with('success', 'App fee saved.');
    }

    /** Update an existing app fee. */
    public function update(AppFeeRequest $request, AppFee $appFee): Response|JsonResponse|RedirectResponse
    {
        $appFee->update($request->validated());

        $fee = AppFee::first();
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'appFee' => $fee], 200);
        }

        return redirect()->route('app-fees.index')->with('success', 'App fee updated.');
    }

    // The resource doesn't support delete; other methods are unused.
}
