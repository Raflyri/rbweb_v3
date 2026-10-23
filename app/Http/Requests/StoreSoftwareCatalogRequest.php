<?php

namespace App\Http\Requests;

use App\Models\SoftwareCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSoftwareCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'                => ['required', 'string', 'max:255'],
            'slug'                 => ['nullable', 'string', 'max:255', 'unique:software_catalogs,slug'],
            'software_category_id' => ['nullable', 'exists:software_categories,id'],
            'category'             => ['nullable', 'string', 'max:100'],
            'version'              => ['required', 'string', 'max:50'],
            'release_date'         => ['nullable', 'date'],
            'badge_text'           => ['nullable', 'string', 'max:100'],
            'short_description'    => ['required', 'string'],
            'description'          => ['nullable', 'string'],
            'tech_stack'           => ['nullable', 'string'],
            'features'             => ['nullable', 'string'],
            'changelog'            => ['nullable', 'string'],
            'demo_url'             => ['nullable', 'url', 'max:500'],
            'demo_type'            => ['required', 'string', 'in:direct,one-click,credentials'],
            'demo_credentials'     => ['nullable', 'string'],
            'thumbnail'            => ['nullable', 'image', 'max:3072'],
            'status'               => ['required', Rule::in(SoftwareCatalog::STATUSES)],
            'is_featured'          => ['nullable', 'boolean'],
            'sort_order'           => ['nullable', 'integer'],
        ];
    }
}
