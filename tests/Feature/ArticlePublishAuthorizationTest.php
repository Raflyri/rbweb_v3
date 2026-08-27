<?php

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Support\ArticlePublishRules;
use App\Models\Article;
use App\Models\User;
use App\Policies\ArticlePolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| Self-publish prevention
|--------------------------------------------------------------------------
| Clients own their drafts but must not be able to put one live. The UI hides
| the option; these tests exercise the layers underneath it, because a crafted
| Livewire request never touches the UI.
*/

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    foreach (['super_admin', 'admin', 'premium', 'regular_user'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
});

function clientUser(string $role = 'regular_user'): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('refuses a client publishing their own article by direct model update', function () {
    $client  = clientUser();
    $article = Article::factory()->pendingReview()->create(['user_id' => $client->id]);

    $this->actingAs($client);

    expect(fn () => $article->update(['status' => 'Published']))
        ->toThrow(AuthorizationException::class);

    expect($article->fresh()->status)->toBe('Pending Review');
});

it('refuses a client scheduling their own article', function () {
    $client  = clientUser();
    $article = Article::factory()->draft()->create(['user_id' => $client->id]);

    $this->actingAs($client);

    expect(fn () => $article->update(['status' => 'Scheduled', 'published_at' => now()->addDay()]))
        ->toThrow(AuthorizationException::class);

    expect($article->fresh()->status)->toBe('Draft');
});

it('refuses a premium client publishing too', function () {
    $client  = clientUser('premium');
    $article = Article::factory()->pendingReview()->create(['user_id' => $client->id]);

    $this->actingAs($client);

    expect(fn () => $article->update(['status' => 'Published']))
        ->toThrow(AuthorizationException::class);
});

it('still lets a client save their own draft and submit for review', function () {
    $client  = clientUser();
    $article = Article::factory()->draft()->create(['user_id' => $client->id]);

    $this->actingAs($client);

    $article->update(['status' => 'Pending Review']);

    expect($article->fresh()->status)->toBe('Pending Review');
});

it('lets an admin publish exactly as before', function () {
    $admin   = clientUser('admin');
    $client  = clientUser();
    $article = Article::factory()->pendingReview()->create(['user_id' => $client->id]);

    $this->actingAs($admin);

    $article->update([
        'status'       => 'Published',
        'reviewer_id'  => $admin->id,
        'reviewed_at'  => now(),
        'published_at' => now(),
    ]);

    expect($article->fresh())
        ->status->toBe('Published')
        ->reviewer_id->toBe($admin->id);
});

it('lets a super admin publish', function () {
    $admin   = clientUser('super_admin');
    $article = Article::factory()->pendingReview()->create();

    $this->actingAs($admin);
    $article->update(['status' => 'Published', 'published_at' => now()]);

    expect($article->fresh()->status)->toBe('Published');
});

it('offers only Draft and Pending Review in the status field for a client', function () {
    $this->actingAs(clientUser());

    expect(array_keys(ArticlePublishRules::statusOptions()))
        ->toBe(['Draft', 'Pending Review']);
});

it('keeps the current status visible so editing a published article does not blank it', function () {
    $client  = clientUser();
    $article = Article::factory()->published()->create(['user_id' => $client->id]);

    $this->actingAs($client);

    $options = ArticlePublishRules::statusOptions($article);

    expect($options)->toHaveKey('Published')
        ->and($options['Published'])->toContain('dikunci');
});

it('offers every status to an admin', function () {
    $this->actingAs(clientUser('admin'));

    expect(array_keys(ArticlePublishRules::statusOptions()))
        ->toBe(['Draft', 'Pending Review', 'Scheduled', 'Published']);
});

it('grants the admin Articles resource to admins and denies it to clients', function () {
    $this->actingAs(clientUser('admin'));
    expect(ArticleResource::canViewAny())->toBeTrue();

    $this->actingAs(clientUser());
    expect(ArticleResource::canViewAny())->toBeFalse();
});

it('lets a client update their own article but not another clients', function () {
    $owner   = clientUser();
    $other   = clientUser();
    $article = Article::factory()->draft()->create(['user_id' => $owner->id]);

    $policy = new ArticlePolicy();

    expect($policy->update($owner, $article))->toBeTrue()
        ->and($policy->update($other, $article))->toBeFalse()
        ->and($policy->publish($owner, $article))->toBeFalse();
});

it('allows unauthenticated console paths such as seeders to publish', function () {
    $article = Article::factory()->pendingReview()->create();

    $article->update(['status' => 'Published', 'published_at' => now()]);

    expect($article->fresh()->status)->toBe('Published');
});
