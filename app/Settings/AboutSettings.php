<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AboutSettings extends Settings
{
    /**
     * Stats grid items: array of items:
     * [
     *   ['value' => '10+', 'label' => ['id' => 'Produk diluncurkan', 'en' => 'Products shipped']],
     *   ...
     * ]
     */
    public array $stats;

    /** Hero section */
    public array $hero_badge;
    public array $hero_title;
    public array $hero_subtitle;

    /** Story & Philosophy */
    public array $story_title;
    public array $story_content;

    /** Vision & Mission */
    public array $vision;
    public array $mission;

    /** Core Values */
    public array $core_values;

    /** Call to Action */
    public array $cta_title;
    public array $cta_subtitle;
    public array $cta_button_text;
    public string $cta_button_url;

    public static function group(): string
    {
        return 'about';
    }
}
