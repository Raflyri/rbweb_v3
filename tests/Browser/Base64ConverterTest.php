<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Base64ConverterTest extends DuskTestCase
{
    public function test_base64_converter_updates_output_in_real_time(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(1000)
                ->script('document.getElementById("rb-b64-input").scrollIntoView({block: "center"});');
                
            $browser->pause(1000)
                ->type('#rb-b64-input', 'RBeverything')
                ->pause(1000)
                ->assertSeeIn('#rb-b64-output', 'UkJldmVyeXRoaW5n');
        });
    }
}
