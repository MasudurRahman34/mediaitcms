<?php

use App\Http\Controllers\Site\ArticleController;
use App\Http\Controllers\Site\CareerController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\MediaController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\ServiceController;
use App\Http\Controllers\Site\SitemapController;
use App\Http\Controllers\Site\SolutionController;
use Illuminate\Support\Facades\Route;

Route::middleware('security')->group(function () {
    Route::get('/', HomeController::class)->name('home');
    Route::get('/about', [PageController::class, 'about'])->name('about');

    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');

    Route::get('/solutions', [SolutionController::class, 'index'])->name('solutions.index');
    Route::get('/solutions/{solution:slug}', [SolutionController::class, 'show'])->name('solutions.show');

    Route::get('/news', [ArticleController::class, 'index'])->name('news.index');
    Route::get('/news/category/{category:slug}', [ArticleController::class, 'category'])->name('news.category');
    Route::get('/news/tag/{tag:slug}', [ArticleController::class, 'tag'])->name('news.tag');
    Route::get('/news/{article:slug}', [ArticleController::class, 'show'])->name('news.show');

    Route::get('/careers', [CareerController::class, 'index'])->name('careers.index');
    Route::get('/careers/{job:slug}', [CareerController::class, 'show'])->name('careers.show');
    Route::post('/careers/{job:slug}/apply', [CareerController::class, 'apply'])
        ->middleware('throttle:apply')
        ->name('careers.apply');

    Route::get('/contact', ContactController::class)->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])
        ->middleware('throttle:contact')
        ->name('contact.store');

    Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
    Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

    // Non-public media (private disk) — authenticated CMS users only.
    Route::get('/media/{media}', MediaController::class)
        ->middleware('auth')
        ->name('media.private');

    // CMS pages (home/about are handled above) — must stay last.
    Route::get('/{slug}', [PageController::class, 'show'])
        ->where('slug', '[a-z0-9\-]+')
        ->name('pages.show');
});
