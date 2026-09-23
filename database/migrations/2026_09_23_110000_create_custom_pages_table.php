<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('path')->unique();
            $table->string('template')->default('verification');
            $table->longText('content');
            $table->boolean('is_active')->default(true)->index();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_robots')->default('noindex, nofollow');
            $table->timestamps();
        });

        // Seed existing electronic document verification pages
        DB::table('custom_pages')->insert([
            [
                'title'            => 'Validasi Dokumen Elektronik',
                'path'             => 'signed/rb',
                'template'         => 'verification',
                'content'          => 'Dokumen ini telah divalidasi dan disetujui secara elektronik oleh <strong>Adistia Bianca Rizki</strong> - Principal Consultant RBEverything. Terima Kasih',
                'is_active'        => true,
                'meta_title'       => 'Validasi Dokumen Elektronik',
                'meta_description' => 'Dokumen ini telah divalidasi dan disetujui secara elektronik oleh Adistia Bianca Rizki - Principal Consultant RBEverything. Terima Kasih',
                'meta_robots'      => 'noindex, nofollow',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'title'            => 'Validasi Dokumen Penawaran Elektronik',
                'path'             => 'offering/rb',
                'template'         => 'verification',
                'content'          => 'Dokumen penawaran ini telah divalidasi dan disetujui secara elektronik oleh <strong>Adistia Bianca Rizki</strong> - Principal Consultant RBEverything. Terima Kasih',
                'is_active'        => true,
                'meta_title'       => 'Validasi Dokumen Penawaran Elektronik',
                'meta_description' => 'Dokumen penawaran ini telah divalidasi dan disetujui secara elektronik oleh Adistia Bianca Rizki - Principal Consultant RBEverything. Terima Kasih',
                'meta_robots'      => 'noindex, nofollow',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_pages');
    }
};
