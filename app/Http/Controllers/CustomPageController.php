<?php

namespace App\Http\Controllers;

use App\Models\CustomPage;
use Illuminate\View\View;

class CustomPageController extends Controller
{
    /**
     * Display the specified custom page by its path.
     */
    public function show(string $path): View
    {
        $normalizedPath = trim($path, "/ \t\n\r\0\x0B");

        $page = CustomPage::where('path', $normalizedPath)
            ->where('is_active', true)
            ->firstOrFail();

        return view('custom-pages.show', compact('page'));
    }
}
