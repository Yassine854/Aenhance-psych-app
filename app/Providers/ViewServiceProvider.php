<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\SitePage;

class ViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // share site pages globally if needed
        View::composer('*', function ($view) {
            $keys = ['mentalTelehealth', 'aenhanceValues', 'youNeedSupport', 'joinOurTeam'];
            $pages = SitePage::whereIn('key', $keys)->get()->keyBy('key')->toArray();
            $view->with('site_pages', $pages);
        });
    }
}
