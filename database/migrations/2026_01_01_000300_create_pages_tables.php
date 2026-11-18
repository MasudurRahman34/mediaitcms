<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('template')->default('standard');
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('published_version_id')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->foreignId('og_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('page_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('label')->nullable();
            $table->json('payload');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['page_id', 'version_number']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->foreign('published_version_id')
                ->references('id')
                ->on('page_versions')
                ->nullOnDelete();
        });

        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('version_id')->nullable()->constrained('page_versions')->cascadeOnDelete();
            $table->string('type');
            $table->string('layout')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->json('content')->nullable();
            $table->timestamps();

            $table->index(['page_id', 'version_id', 'sort_order'], 'page_sections_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_sections');

        Schema::table('pages', function (Blueprint $table) {
            $table->dropForeign(['published_version_id']);
        });

        Schema::dropIfExists('page_versions');
        Schema::dropIfExists('pages');
    }
};
