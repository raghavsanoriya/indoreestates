<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class CapturedPageController extends Controller
{
    public function show(string $page): View
    {
        $titles = [
            'about-us.html' => 'About indoreestate',
            'our-service.html' => 'Our Services',
            'faq.html' => 'Frequently Asked Questions',
            'privacy-policy.html' => 'Privacy Enquiries',
            'pricing.html' => 'Listing Enquiries',
            'blog.html' => 'Property Updates',
            'blog-grid.html' => 'Property Updates',
        ];
        abort_unless(isset($titles[$page]), 404);

        return view('static-page', ['page' => $page, 'title' => $titles[$page]]);
    }
}
