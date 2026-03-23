<?php

use App\Models\Article;
use Illuminate\Support\Facades\Config;

it('falls back to English when Japanese translation is blank', function () {
    // Ensure English is the fallback locale
    Config::set('app.fallback_locale', 'en');

    $article = Article::create([
        'title' => [
            'en' => 'English Title',
            'ja' => '',
        ],
        'content' => [
            'en' => 'English Content',
            'ja' => '',
        ],
        'status' => 'Published',
        'slug' => 'test-article-1',
    ]);

    // Retrieve the article to test the accessor or getTranslation method
    $freshArticle = Article::find($article->id);
    
    // In Spatie Translatable, by default, an empty string might not trigger a fallback, 
    // unless fallback is explicitly requested. Let's configure it if needed or test the standard method.
    // If the Japanese translation is explicitly missing or blank, we expect it to not throw 500
    // and ideally fall back to English if requested with fallback.
    
    // Typical frontend usage is just getting the attribute for the current app locale.
    app()->setLocale('ja');
    
    // Spatie translatable returns empty if strictly asked, but when fallback is enabled, it should return 'en'.
    // The prompt says: "Assert that the system automatically returns the English (en) text and does not cause a 500 error."
    $title = $freshArticle->title; 
    
    expect($title)->toBe('English Title');
    
    app()->setLocale('en'); // Reset
});
