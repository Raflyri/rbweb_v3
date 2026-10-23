<?php

namespace App\Http\Controllers;

use App\Models\SoftwareCatalog;
use App\Models\SoftwareCategory;
use Illuminate\View\View;

class SoftwareCatalogFrontendController extends Controller
{
    /**
     * Display all published ('Tayangkan') software in the Software Catalog portal.
     */
    public function index(): View
    {
        $items = SoftwareCatalog::with('softwareCategory')
            ->published()
            ->orderBy('sort_order', 'asc')
            ->latest('release_date')
            ->get();

        // Fetch dynamic categories that have published software with count
        $categories = SoftwareCategory::whereHas('publishedSoftware')
            ->orderBy('sort_order', 'asc')
            ->withCount('publishedSoftware')
            ->get();

        return view('software-catalog.index', compact('items', 'categories'));
    }

    /**
     * Display detailed showcase page for a specific software item.
     */
    public function show(string $slug): View
    {
        $item = SoftwareCatalog::with('softwareCategory')
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedItems = SoftwareCatalog::with('softwareCategory')
            ->published()
            ->where('id', '!=', $item->id)
            ->when($item->software_category_id, fn ($q) => $q->where('software_category_id', $item->software_category_id))
            ->take(3)
            ->get();

        if ($relatedItems->isEmpty()) {
            $relatedItems = SoftwareCatalog::with('softwareCategory')
                ->published()
                ->where('id', '!=', $item->id)
                ->latest()
                ->take(3)
                ->get();
        }

        return view('software-catalog.show', compact('item', 'relatedItems'));
    }
}
