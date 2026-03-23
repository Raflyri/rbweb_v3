<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LanguageSwitcherTest extends DuskTestCase
{
    public function test_language_switcher_changes_text_or_locale(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->waitFor('.rb-lang-switcher');

            // Find the desktop ID button and click it
            $browser->click('.rb-desktop-nav .rb-lang-btn[data-lang="id"]')
                ->pause(1000); // Wait for JS to update the UI or page to reload

            // Assert html lang attribute has changed via script evaluation
            $langAttr = $browser->script("return document.documentElement.lang;")[0] ?? '';
            $this->assertEquals('id', $langAttr);
            
            // Check that the translated UI text appears instead of English
            $browser->assertDontSee('Everything you need for')
                    ->assertSee('Semua yang Anda');
        });
    }
}
