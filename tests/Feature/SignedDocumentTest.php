<?php

use App\Models\CustomPage;

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

it('renders any dynamic custom page created in database', function () {
    CustomPage::create([
        'title'     => 'Halaman Contoh Khusus',
        'path'      => 'info/mitra-terverifikasi',
        'template'  => 'standard',
        'content'   => '<p>Halaman ini berhasil dibuat secara dinamis dari database.</p>',
        'is_active' => true,
    ]);

    $response = $this->get('/info/mitra-terverifikasi');
    $response->assertOk();
    $response->assertSee('Halaman Contoh Khusus');
    $response->assertSee('Halaman ini berhasil dibuat secara dinamis dari database.', false);
});

it('returns 404 when a custom page is deactivated', function () {
    $page = CustomPage::create([
        'title'     => 'Halaman Nonaktif',
        'path'      => 'info/halaman-rahasia',
        'template'  => 'verification',
        'content'   => 'Tidak boleh terlihat',
        'is_active' => false,
    ]);

    $response = $this->get('/info/halaman-rahasia');
    $response->assertNotFound();
});

it('returns 404 for nonexistent custom page paths', function () {
    $response = $this->get('/halaman-yang-tidak-pernah-ada-xyz-123');
    $response->assertNotFound();
});
