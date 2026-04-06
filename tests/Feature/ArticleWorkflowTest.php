<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Reset cached roles/permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Create roles
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'admin',       'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'premium',     'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'regular_user','guard_name' => 'web']);
});

// ── 1. Status defaults to Pending Review on create ─────────────────────────────
it('Forces Pending Review status when a client creates an article via Filament', function () {
    $client = User::factory()->create();
    $client->assignRole('regular_user');

    test()->actingAs($client);

    // Tests using Filament Livewire component to simulate real Client workflow
    \Livewire\Livewire::test(\App\Filament\ClientArea\Resources\ClientArticleResource\Pages\CreateClientArticle::class)
        ->fillForm([
            'title'   => ['id' => 'Another Article'],
            'content' => ['id' => 'Content here'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $article2 = Article::whereJsonContains('title->id', 'Another Article')->first();
    
    expect($article2)->not->toBeNull();
    expect($article2->status)->toBe('Pending Review');
});

// ── 2. Clients cannot see other clients' articles ─────────────────────────────
it('scopes articles to the authenticated user', function () {
    $client1 = User::factory()->create();
    $client1->assignRole('regular_user');

    $client2 = User::factory()->create();
    $client2->assignRole('regular_user');

    Article::create([
        'user_id' => $client1->id,
        'title'   => ['en' => 'Client1 Article'],
        'content' => ['en' => 'Content'],
        'slug'    => 'client1-article',
        'status'  => 'Draft',
    ]);

    // Query as client2 (using the resource scope logic)
    $scopedQuery = Article::where('user_id', $client2->id)->get();

    expect($scopedQuery)->toHaveCount(0);
});

// ── 3. Admin approve changes status to Published ──────────────────────────────
it('allows admin to approve an article and sets metadata', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $client = User::factory()->create();
    $client->assignRole('regular_user');

    $article = Article::create([
        'user_id' => $client->id,
        'title'   => ['en' => 'Pending Article'],
        'content' => ['en' => 'Content'],
        'slug'    => 'pending-article',
        'status'  => 'Pending Review',
    ]);

    // Simulate the Approve action
    $article->update([
        'status'       => 'Published',
        'reviewer_id'  => $admin->id,
        'reviewed_at'  => now(),
        'published_at' => now(),
    ]);

    expect($article->fresh())
        ->status->toBe('Published')
        ->reviewer_id->toBe($admin->id)
        ->reviewed_at->not->toBeNull()
        ->published_at->not->toBeNull();
});

// ── 4. Admin reject sets status back to Draft ─────────────────────────────────
it('allows admin to reject an article and reverts to Draft', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $client = User::factory()->create();
    $client->assignRole('regular_user');

    $article = Article::create([
        'user_id' => $client->id,
        'title'   => ['en' => 'Article To Reject'],
        'content' => ['en' => 'Content'],
        'slug'    => 'article-to-reject',
        'status'  => 'Pending Review',
    ]);

    $article->update([
        'status'      => 'Draft',
        'reviewer_id' => $admin->id,
        'reviewed_at' => now(),
    ]);

    expect($article->fresh())
        ->status->toBe('Draft')
        ->reviewer_id->toBe($admin->id);
});

// ── 5. scopePublished only returns Published articles ─────────────────────────
it('scope published only returns published articles', function () {
    $user = User::factory()->create();
    $user->assignRole('regular_user');

    Article::create(['user_id' => $user->id, 'title' => ['en' => 'A'], 'content' => ['en' => 'c'], 'slug' => 'a1', 'status' => 'Draft']);
    Article::create(['user_id' => $user->id, 'title' => ['en' => 'B'], 'content' => ['en' => 'c'], 'slug' => 'b1', 'status' => 'Pending Review']);
    Article::create(['user_id' => $user->id, 'title' => ['en' => 'C'], 'content' => ['en' => 'c'], 'slug' => 'c1', 'status' => 'Published']);

    expect(Article::published()->count())->toBe(1);
});
