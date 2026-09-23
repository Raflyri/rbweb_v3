<?php

it('renders the signed document validation page with expected text and layout', function () {
    $response = $this->get('/signed/rb');

    $response->assertOk();
    $response->assertSee('Dokumen ini telah divalidasi dan disetujui secara elektronik oleh');
    $response->assertSee('Adistia Bianca Rizki');
    $response->assertSee('Principal Consultant RBEverything');
    $response->assertSee('Terima Kasih');
    // Ensure header and footer elements exist
    $response->assertSee('RBeverything');
});

it('renders the offering document validation page with expected text and layout', function () {
    $response = $this->get('/offering/rb');

    $response->assertOk();
    $response->assertSee('Dokumen penawaran ini telah divalidasi dan disetujui secara elektronik oleh');
    $response->assertSee('Adistia Bianca Rizki');
    $response->assertSee('Principal Consultant RBEverything');
    $response->assertSee('Terima Kasih');
    // Ensure header and footer elements exist
    $response->assertSee('RBeverything');
});
