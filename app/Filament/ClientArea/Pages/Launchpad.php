<?php

namespace App\Filament\ClientArea\Pages;

use App\Models\LaunchpadLink;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class Launchpad extends Page
{
    protected string $view = 'filament.client-area.pages.launchpad';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Launchpad';

    protected static ?string $title = 'My Launchpad';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    public function getLinks(): Collection
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return LaunchpadLink::query()
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->map(function (LaunchpadLink $link) use ($user) {
                $hasAccess = ! $link->required_permission
                    || $user->can($link->required_permission);

                return [
                    'id'          => $link->id,
                    'title'       => $link->title,
                    'description' => $link->description,
                    'icon'        => $link->icon ?? 'squares-2x2',
                    'url'         => $link->url,
                    'is_external' => $link->is_external,
                    'has_access'  => $hasAccess,
                ];
            });
    }

    protected function getViewData(): array
    {
        return [
            'links' => $this->getLinks(),
        ];
    }
}
