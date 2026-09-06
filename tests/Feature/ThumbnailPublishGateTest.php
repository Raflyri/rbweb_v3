<?php

use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Models\Article;
use App\Models\User;
use App\Support\ArticleContent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| Reproduction: "Thumbnail wajib diisi" with a thumbnail present
|--------------------------------------------------------------------------
| Reported from production on /rbdashboard/articles/5/edit: the publish gate
| refused to let an article go Published even though a thumbnail had been
| uploaded and showed "Upload complete".
*/

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    foreach (['super_admin', 'admin'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    test()->actingAs($admin);

    \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));

    Storage::fake('public');
    Storage::disk('public')->put('article-thumbnails/existing.jpg', 'fake-bytes');
});

function publishableArticle(array $overrides = []): Article
{
    return Article::factory()->create(array_merge([
        'status'  => 'Draft',
        'content' => ['id' => '<p>' . str_repeat('kata ', 80) . '</p>'],
        'title'   => ['id' => 'Artikel Uji Thumbnail'],
    ], $overrides));
}

it('publishes when the article already has a saved thumbnail', function () {
    $article = publishableArticle(['thumbnail' => 'article-thumbnails/existing.jpg']);

    Livewire::test(EditArticle::class, ['record' => $article->getKey()])
        ->fillForm(['status' => 'Published'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($article->fresh()->status)->toBe('Published');
});

it('publishes when a thumbnail is uploaded in the same edit', function () {
    $article = publishableArticle(['thumbnail' => null]);

    Livewire::test(EditArticle::class, ['record' => $article->getKey()])
        ->fillForm([
            'thumbnail' => UploadedFile::fake()->image('microsoft-admin.jpg', 1200, 675),
            'status'    => 'Published',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($article->fresh()->status)->toBe('Published');
});

it('still refuses to publish when there is genuinely no thumbnail', function () {
    $article = publishableArticle(['thumbnail' => null]);

    Livewire::test(EditArticle::class, ['record' => $article->getKey()])
        ->fillForm(['status' => 'Published'])
        ->call('save')
        ->assertHasFormErrors(['status']);

    expect($article->fresh()->status)->toBe('Draft');
});

/*
| The shapes hasThumbnail() has to recognise. The uploaded-file object is the
| one that was missing: $get('thumbnail') returns it during validation
| immediately after an upload, before the file is moved to its permanent path.
*/
it('recognises every shape a thumbnail state can take', function () {
    $file = UploadedFile::fake()->image('x.jpg');

    expect(ArticleContent::hasThumbnail('article-thumbnails/x.jpg'))->toBeTrue()
        ->and(ArticleContent::hasThumbnail(['uuid' => 'article-thumbnails/x.jpg']))->toBeTrue()
        ->and(ArticleContent::hasThumbnail($file))->toBeTrue()
        ->and(ArticleContent::hasThumbnail(['uuid' => $file]))->toBeTrue()
        ->and(ArticleContent::hasThumbnail(null))->toBeFalse()
        ->and(ArticleContent::hasThumbnail(''))->toBeFalse()
        ->and(ArticleContent::hasThumbnail('   '))->toBeFalse()
        ->and(ArticleContent::hasThumbnail([]))->toBeFalse()
        ->and(ArticleContent::hasThumbnail(['uuid' => null]))->toBeFalse();
});
