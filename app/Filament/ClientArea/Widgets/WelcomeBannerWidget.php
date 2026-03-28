<?php

namespace App\Filament\ClientArea\Widgets;

use App\Models\Article;
use Filament\Widgets\Widget;

class WelcomeBannerWidget extends Widget
{
    protected string $view = 'filament.client-area.widgets.welcome-banner';
    protected static ?int $sort = -1;
    protected int|string|array $columnSpan = 'full';

    public string $userName  = '';
    public string $userRole  = '';
    public int    $totalArticles   = 0;
    public int    $pendingArticles = 0;
    public int    $publishedArticles = 0;

    public function mount(): void
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $this->userName = $user->name;
        $this->userRole = $user->getRoleNames()->first() ?? 'User';

        $this->totalArticles     = Article::where('user_id', $user->id)->count();
        $this->pendingArticles   = Article::where('user_id', $user->id)->where('status', 'Pending Review')->count();
        $this->publishedArticles = Article::where('user_id', $user->id)->where('status', 'Published')->count();
    }
}
