<?php

namespace App\Http\Controllers;

use App\Support\Cms;
use Illuminate\Contracts\View\View;

class CapturedPageController extends Controller
{
    /** Public page addresses and the website content (config/cms.php) that fills them. */
    private const PAGES = [
        'about-us.html' => 'about',
        'our-service.html' => 'services',
        'faq.html' => 'faq',
        'privacy-policy.html' => 'privacy',
        'pricing.html' => 'pricing',
        'blog.html' => 'blog',
        'blog-grid.html' => 'blog',
    ];

    public function show(Cms $cms, string $page): View
    {
        abort_unless(isset(self::PAGES[$page]), 404);
        $slug = self::PAGES[$page];

        return view('static-page', ['page' => $page, 'slug' => $slug, 'title' => $cms->text("pages.$slug.title")]);
    }
}
