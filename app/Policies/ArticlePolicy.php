<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Article;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Authorisation for articles.
 *
 * The editorial rule is draft → pending review → (admin review) → published.
 * Clients own their drafts but must never be able to put one live themselves,
 * so publishing is a separate ability from updating.
 */
class ArticlePolicy
{
    use HandlesAuthorization;

    /** Roles allowed to move an article to a publicly visible status. */
    public const PUBLISHER_ROLES = ['super_admin', 'admin', 'reviewer'];

    /** Statuses that make an article publicly reachable. */
    public const PUBLIC_STATUSES = ['Published', 'Scheduled'];

    /**
     * May this user publish, schedule, or approve articles?
     *
     * Static so the model observer and the Filament forms can ask the same
     * question without resolving the whole policy.
     */
    public static function userCanPublish(?AuthUser $user): bool
    {
        return $user !== null
            && method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(static::PUBLISHER_ROLES);
    }

    public function viewAny(AuthUser $authUser): bool
    {
        // The listing itself is scoped to the owner in ClientArticleResource;
        // any signed-in user may see their own list.
        return true;
    }

    public function view(AuthUser $authUser, Article $article): bool
    {
        return $this->owns($authUser, $article) || static::userCanPublish($authUser);
    }

    public function create(AuthUser $authUser): bool
    {
        return true;
    }

    public function update(AuthUser $authUser, Article $article): bool
    {
        return $this->owns($authUser, $article) || static::userCanPublish($authUser);
    }

    public function delete(AuthUser $authUser, Article $article): bool
    {
        return $this->owns($authUser, $article) || static::userCanPublish($authUser);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return static::userCanPublish($authUser);
    }

    public function restore(AuthUser $authUser, Article $article): bool
    {
        return static::userCanPublish($authUser);
    }

    public function forceDelete(AuthUser $authUser, Article $article): bool
    {
        return static::userCanPublish($authUser);
    }

    /**
     * May this user move the article into a publicly visible status?
     * Owning the article is explicitly NOT enough.
     */
    public function publish(AuthUser $authUser, ?Article $article = null): bool
    {
        return static::userCanPublish($authUser);
    }

    /** May this user approve or reject someone else's submission? */
    public function review(AuthUser $authUser, Article $article): bool
    {
        return static::userCanPublish($authUser);
    }

    protected function owns(AuthUser $authUser, Article $article): bool
    {
        return $article->user_id !== null
            && (int) $article->user_id === (int) $authUser->getAuthIdentifier();
    }
}
