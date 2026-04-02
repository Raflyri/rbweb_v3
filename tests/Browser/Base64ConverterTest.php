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
                // 1. Langsung gulir ke elemennya terlebih dahulu untuk memicu animasi
                ->scrollIntoView('#rb-b64-input')

                // 2. Jeda yang cukup (misal 2 detik) untuk memastikan animasi data-reveal benar-benar selesai
                ->pause(2000)

                // 3. Pastikan elemen sudah terdeteksi dan siap menerima input
                ->waitFor('#rb-b64-input')

                // 4. Lakukan interaksi
                ->type('#rb-b64-input', 'RBeverything')
                ->pause(1000)
                ->assertSeeIn('#rb-b64-output', 'UkJldmVyeXRoaW5n');
        });
    }
}
