<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| GET /storage/{path}
|--------------------------------------------------------------------------
| Replaces the public/storage symlink, which cannot be created on this host:
| symlink() and exec() are both disabled, so storage:link fails with
| "Call to undefined function exec()". Uploaded thumbnails were stored fine
| but every URL 404'd.
*/

beforeEach(function () {
    Storage::fake('public');
});

it('serves a file that exists on the public disk', function () {
    Storage::disk('public')->put('article-thumbnails/thumb.jpg', 'binary-content-here');

    $this->get('/storage/article-thumbnails/thumb.jpg')
        ->assertOk()
        ->assertStreamedContent('binary-content-here');
});

it('serves a real uploaded image the way Filament stores one', function () {
    $path = UploadedFile::fake()->image('shield.jpg', 800, 450)
        ->store('article-thumbnails', 'public');

    $this->get('/storage/' . $path)->assertOk();
});

it('sends a long immutable cache header so Cloudflare absorbs the traffic', function () {
    Storage::disk('public')->put('article-thumbnails/thumb.jpg', 'x');

    $response = $this->get('/storage/article-thumbnails/thumb.jpg');

    expect($response->headers->get('Cache-Control'))->toContain('max-age=31536000')
        ->and($response->headers->get('Cache-Control'))->toContain('immutable')
        ->and($response->headers->get('Last-Modified'))->not->toBeNull();
});

it('404s for a file that is not there', function () {
    $this->get('/storage/article-thumbnails/missing.jpg')->assertNotFound();
});

it('refuses to climb out of the disk root', function () {
    foreach ([
        '/storage/../.env',
        '/storage/article-thumbnails/../../../.env',
        '/storage/..%2F..%2F.env',
    ] as $attempt) {
        $response = $this->get($attempt);

        expect($response->status())->not->toBe(200);
    }
});

it('does not shadow the blog routes', function () {
    $this->get(route('blog.index'))->assertOk();
});
