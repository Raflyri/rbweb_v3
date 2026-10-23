<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSoftwareCatalogRequest;
use App\Http\Requests\UpdateSoftwareCatalogRequest;
use App\Models\SoftwareCatalog;
use App\Models\SoftwareCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SoftwareCatalogController extends Controller
{
    /**
     * Display a listing of software catalog items.
     */
    public function index(): View
    {
        $items = SoftwareCatalog::with('softwareCategory')
            ->orderBy('sort_order', 'asc')
            ->latest('release_date')
            ->paginate(15);

        return view('rbdashboard.software-catalog.index', compact('items'));
    }

    /**
     * Show the form for creating a new software item.
     */
    public function create(): View
    {
        $categories = SoftwareCategory::orderBy('sort_order', 'asc')->get();

        return view('rbdashboard.software-catalog.create', compact('categories'));
    }

    /**
     * Store a newly created software item in storage.
     */
    public function store(StoreSoftwareCatalogRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['slug'] = ! empty($data['slug'])
            ? Str::slug($data['slug'])
            : SoftwareCatalog::generateUniqueSlug($data['title']);

        // Sync fallback category string with relationship name
        if (! empty($data['software_category_id'])) {
            $cat = SoftwareCategory::find($data['software_category_id']);
            if ($cat) {
                $data['category'] = $cat->name;
            }
        }

        if (! empty($data['tech_stack']) && is_string($data['tech_stack'])) {
            $data['tech_stack'] = array_values(array_filter(array_map('trim', explode(',', $data['tech_stack']))));
        }

        if (! empty($data['features']) && is_string($data['features'])) {
            $data['features'] = array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $data['features']))));
        }

        $data['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('software-thumbnails', 'public');
        }

        SoftwareCatalog::create($data);

        return redirect()
            ->route('software-catalog.index')
            ->with('success', 'Software berhasil ditambahkan ke katalog.');
    }

    /**
     * Show the form for editing the specified software item.
     */
    public function edit(SoftwareCatalog $software_catalog): View
    {
        $categories = SoftwareCategory::orderBy('sort_order', 'asc')->get();

        return view('rbdashboard.software-catalog.edit', [
            'item'       => $software_catalog,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified software item in storage.
     */
    public function update(UpdateSoftwareCatalogRequest $request, SoftwareCatalog $software_catalog): RedirectResponse
    {
        $data = $request->validated();

        $data['slug'] = ! empty($data['slug'])
            ? Str::slug($data['slug'])
            : SoftwareCatalog::generateUniqueSlug($data['title'], $software_catalog->id);

        if (! empty($data['software_category_id'])) {
            $cat = SoftwareCategory::find($data['software_category_id']);
            if ($cat) {
                $data['category'] = $cat->name;
            }
        }

        if (! empty($data['tech_stack']) && is_string($data['tech_stack'])) {
            $data['tech_stack'] = array_values(array_filter(array_map('trim', explode(',', $data['tech_stack']))));
        }

        if (! empty($data['features']) && is_string($data['features'])) {
            $data['features'] = array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $data['features']))));
        }

        $data['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('thumbnail')) {
            if ($software_catalog->thumbnail && Storage::disk('public')->exists($software_catalog->thumbnail)) {
                Storage::disk('public')->delete($software_catalog->thumbnail);
            }

            $data['thumbnail'] = $request->file('thumbnail')->store('software-thumbnails', 'public');
        } else {
            unset($data['thumbnail']);
        }

        $software_catalog->update($data);

        return redirect()
            ->route('software-catalog.index')
            ->with('success', 'Software berhasil diperbarui.');
    }

    /**
     * Remove the specified software item from storage.
     */
    public function destroy(SoftwareCatalog $software_catalog): RedirectResponse
    {
        if ($software_catalog->thumbnail && Storage::disk('public')->exists($software_catalog->thumbnail)) {
            Storage::disk('public')->delete($software_catalog->thumbnail);
        }

        $software_catalog->delete();

        return redirect()
            ->route('software-catalog.index')
            ->with('success', 'Software berhasil dihapus dari katalog.');
    }
}
