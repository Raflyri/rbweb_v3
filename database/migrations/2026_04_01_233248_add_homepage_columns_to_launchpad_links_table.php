<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('launchpad_links', function (Blueprint $table) {
            // ── Homepage product card fields ──────────────────────────────────
            $table->boolean('show_on_homepage')->default(false)->after('is_active');
            $table->string('homepage_badge')->nullable()->after('show_on_homepage');      // e.g. "AI · Computer Vision"
            $table->string('homepage_accent')->nullable()->default('sky')->after('homepage_badge'); // violet|sky|emerald|rose|amber
            $table->string('version')->nullable()->after('homepage_accent');              // e.g. "v2.4"
            $table->string('homepage_cta_label')->nullable()->after('version');           // e.g. "Open Tool"
            $table->string('card_template')->nullable()->default('generic')->after('homepage_cta_label'); // liveness|base64|portfolio|generic
        });
    }

    public function down(): void
    {
        Schema::table('launchpad_links', function (Blueprint $table) {
            $table->dropColumn([
                'show_on_homepage',
                'homepage_badge',
                'homepage_accent',
                'version',
                'homepage_cta_label',
                'card_template',
            ]);
        });
    }
};

