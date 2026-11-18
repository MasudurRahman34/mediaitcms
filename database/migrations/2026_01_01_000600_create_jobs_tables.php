<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->string('employment_type')->default('full-time');
            $table->string('experience_level')->nullable();
            $table->string('salary_range')->nullable();
            $table->longText('description')->nullable();
            $table->longText('requirements')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('closing_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamps();

            $table->index(['status', 'closing_at']);
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('cv_path');
            $table->string('cv_original_name');
            $table->string('cv_mime');
            $table->unsignedBigInteger('cv_size')->default(0);
            $table->string('status')->default('new');
            $table->string('source')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['job_id', 'status']);
            $table->index('email');
        });

        Schema::create('application_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_notes');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('jobs');
    }
};
