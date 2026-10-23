<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('software_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->default('Enterprise');
            $table->string('version')->default('1.0');
            $table->string('badge_text')->nullable();
            $table->text('short_description');
            $table->longText('description')->nullable();
            $table->json('tech_stack')->nullable();
            $table->json('features')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('demo_type')->default('direct');
            $table->text('demo_credentials')->nullable();
            $table->enum('status', ['Tayangkan', 'Draft'])->default('Draft');
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software_catalogs');
    }
};
