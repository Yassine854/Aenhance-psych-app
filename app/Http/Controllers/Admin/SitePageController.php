<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSitePageRequest;
use App\Http\Requests\UpdateSitePageRequest;
use App\Models\SitePage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SitePageController extends Controller
{
    public function index(Request $request)
    {
        $pages = SitePage::orderBy('id', 'desc')->paginate(15);
        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
            'filters' => $request->only(['search_field', 'search_query']),
            'status' => session('status'),
            'error' => session('error'),
        ]);
    }

    public function create()
    {
        $keys = ['mentalTelehealth', 'aenhanceValues', 'youNeedSupport', 'joinOurTeam'];
        $used = SitePage::pluck('key')->all();

        return Inertia::render('Admin/Pages/Create', [
            'keys' => $keys,
            'usedKeys' => $used,
        ]);
    }

    public function store(StoreSitePageRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        SitePage::create($data);

        return redirect()->route('admin.pages.index')->with('status', 'Page created');
    }

    public function edit(SitePage $page)
    {
        $keys = ['mentalTelehealth', 'aenhanceValues', 'youNeedSupport', 'joinOurTeam'];
        // keys used by other pages (allow current page's key)
        $used = SitePage::where('id', '!=', $page->id)->pluck('key')->all();

        return Inertia::render('Admin/Pages/Edit', [
            'page' => $page,
            'keys' => $keys,
            'usedKeys' => $used,
        ]);
    }

    public function update(UpdateSitePageRequest $request, SitePage $page)
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $page->update($data);

        return redirect()->route('admin.pages.index')->with('status', 'Page updated');
    }

    public function show(SitePage $page)
    {
        return Inertia::render('Admin/Pages/Show', ['page' => $page]);
    }

    public function destroy(SitePage $page)
    {
        $page->delete();
        return redirect()->route('admin.pages.index')->with('status', 'Page deleted');
    }
}
