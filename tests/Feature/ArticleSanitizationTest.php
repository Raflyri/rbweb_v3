<?php

use App\Models\Article;

/*
|--------------------------------------------------------------------------
| Article content sanitisation
|--------------------------------------------------------------------------
| blog/show.blade.php renders the body with {!! !!}, so anything stored in
| the content column runs in the reader's browser. It has to be purified on
| the way in — without flattening the RichEditor's legitimate formatting.
*/

it('strips a script tag from article content on save', function () {
    $article = Article::factory()->create([
        'content' => ['en' => '<p>Hello</p><script>alert(1)</script>'],
    ]);

    $stored = $article->fresh()->getTranslation('content', 'en', false);

    expect($stored)->not->toContain('<script>')
        ->and($stored)->not->toContain('alert(1)')
        ->and($stored)->toContain('Hello');
});

it('strips inline event handlers and javascript: links', function () {
    $article = Article::factory()->create([
        'content' => ['en' => '<p onclick="steal()">x</p><a href="javascript:steal()">y</a>'],
    ]);

    $stored = $article->fresh()->getTranslation('content', 'en', false);

    expect($stored)->not->toContain('onclick')
        ->and($stored)->not->toContain('javascript:');
});

it('sanitises every locale, not just the active one', function () {
    $article = Article::factory()->create([
        'content' => [
            'en' => '<p>ok</p><script>a()</script>',
            'id' => '<p>oke</p><script>b()</script>',
            'ja' => '<p>ok</p><script>c()</script>',
        ],
    ]);

    $fresh = $article->fresh();

    foreach (['en', 'id', 'ja'] as $locale) {
        expect($fresh->getTranslation('content', $locale, false))->not->toContain('<script>');
    }
});

it('preserves the formatting the RichEditor can produce', function () {
    $body = '<h2>Heading</h2>'
        . '<p><strong>bold</strong> <em>italic</em> <u>underline</u> <s>strike</s></p>'
        . '<a href="https://example.com" title="t">link</a>'
        . '<ul><li>bullet</li></ul><ol><li>numbered</li></ol>'
        . '<blockquote>quote</blockquote>'
        . '<pre><code>echo 1;</code></pre>'
        . '<table><tr><td>cell</td></tr></table>'
        . '<img src="/img/a.png" alt="alt text">';

    $article = Article::factory()->create(['content' => ['en' => $body]]);

    $stored = $article->fresh()->getTranslation('content', 'en', false);

    foreach (['<h2>', '<strong>', '<em>', '<u>', '<s>', '<ul>', '<li>', '<ol>',
              '<blockquote>', '<pre>', '<code>', '<table>', '<td>', '<img'] as $tag) {
        expect($stored)->toContain($tag);
    }

    expect($stored)->toContain('href="https://example.com"')
        ->and($stored)->toContain('alt="alt text"');
});

it('does not render a script tag on the public article page', function () {
    Article::factory()->published()->create([
        'title'   => ['en' => 'XSS Probe', 'id' => 'XSS Probe'],
        'slug'    => ['en' => 'xss-probe', 'id' => 'xss-probe'],
        'content' => ['en' => '<p>safe</p><script>alert(document.cookie)</script>'],
    ]);

    $this->get('/blog/xss-probe')
        ->assertOk()
        ->assertDontSee('alert(document.cookie)', false);
});
