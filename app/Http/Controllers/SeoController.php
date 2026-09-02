<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $sitemap = route('seo.sitemap');

        $body = implode("\n", [
            'User-agent: *',
            'Disallow:',
            '',
            "Sitemap: {$sitemap}",
            '',
        ]);

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        $urls = [];

        foreach (config('seo.sitemap_routes', []) as $routeName => $priority) {
            if (! Route::has($routeName)) {
                continue;
            }

            $urls[] = [
                'loc' => route($routeName),
                'priority' => $priority,
            ];
        }

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
