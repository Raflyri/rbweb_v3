<?php

namespace App\Observers;

use App\Models\Profile;
use App\Observers\Concerns\RefreshesSitemap;

/**
 * Keeps sitemap.xml in step with the public portfolio pages at /@{slug}.
 */
class ProfileObserver
{
    use RefreshesSitemap;

    public function saved(Profile $profile): void
    {
        if ($this->isPublic($profile) || $profile->wasChanged('custom_url_slug')) {
            $this->refreshSitemapSoon();
        }
    }

    public function deleted(Profile $profile): void
    {
        if ($this->isPublic($profile)) {
            $this->refreshSitemapSoon();
        }
    }

    protected function isPublic(Profile $profile): bool
    {
        return is_string($profile->custom_url_slug) && trim($profile->custom_url_slug) !== '';
    }
}
