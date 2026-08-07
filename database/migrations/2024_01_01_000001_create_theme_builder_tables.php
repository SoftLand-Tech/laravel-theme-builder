<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Themes + theme revisions. Mirrors the host app's schema exactly so an
 * existing install keeps its data; fresh package installs publish this.
 *
 * `store_id` and `store_user_id` are intentionally left WITHOUT a foreign key
 * constraint: the package is tenant-agnostic by default. Hosts may add an FK
 * migration pointing at their own tenants/users table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table): void {
            $table->id();
            // Nullable so global preset themes can exist without an owning tenant.
            $table->foreignId('store_id')->nullable();

            $table->json('name'); // {ar, en}
            $table->text('description')->nullable();

            // Preset identification: a global theme available to all tenants.
            $table->boolean('is_preset')->default(false);
            $table->string('preset_slug')->nullable();
            $table->string('preview_image')->nullable();

            $table->string('status', 32)->default('draft');
            $table->json('blocks')->nullable();
            $table->json('settings')->nullable();
            $table->json('templates')->nullable();

            $table->json('header')->nullable();
            $table->json('footer')->nullable();

            $table->json('draft_blocks')->nullable();
            $table->json('draft_settings')->nullable();
            $table->json('draft_templates')->nullable();
            $table->json('draft_header')->nullable();
            $table->json('draft_footer')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status']);
            $table->index(['is_preset', 'preset_slug']);
        });

        Schema::create('theme_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('store_user_id')->nullable();
            $table->unsignedInteger('revision_number');
            $table->string('version', 16)->nullable();
            $table->string('change_summary')->nullable();
            $table->json('blocks');
            $table->json('settings')->nullable();

            $table->json('header')->nullable();
            $table->json('footer')->nullable();
            $table->json('templates')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['theme_id', 'revision_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_revisions');
        Schema::dropIfExists('themes');
    }
};
