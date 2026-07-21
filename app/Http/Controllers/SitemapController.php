<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index(Request $request)
    {
        $urls = [
            url('/'),
            url('/blogs'),
            url('/ressources'),
            url('/services'),
        ];

        try {
            $blogs = Blog::whereNotNull('published_at')->orderBy('published_at', 'desc')->get(['slug']);
            foreach ($blogs as $b) {
                $urls[] = url('/blogs/' . $b->slug);
            }
        } catch (\Throwable $e) {
            // If Blog model/table isn't available at runtime, skip listing posts.
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . e($u) . "</loc>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "  </url>\n";
        }
        $xml .= "</urlset>";

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
