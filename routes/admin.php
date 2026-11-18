<?php

use App\Http\Controllers\Admin\AdminShellController;
use App\Http\Controllers\Admin\Api\ApplicationsController;
use App\Http\Controllers\Admin\Api\ArticlesController;
use App\Http\Controllers\Admin\Api\AuditLogsController;
use App\Http\Controllers\Admin\Api\CategoriesController;
use App\Http\Controllers\Admin\Api\DashboardController;
use App\Http\Controllers\Admin\Api\EnquiriesController;
use App\Http\Controllers\Admin\Api\JobsController;
use App\Http\Controllers\Admin\Api\MediaController;
use App\Http\Controllers\Admin\Api\NavigationController;
use App\Http\Controllers\Admin\Api\PagesController;
use App\Http\Controllers\Admin\Api\PageSectionsController;
use App\Http\Controllers\Admin\Api\PlacementsController;
use App\Http\Controllers\Admin\Api\RolesController;
use App\Http\Controllers\Admin\Api\ServicesController;
use App\Http\Controllers\Admin\Api\SettingsController;
use App\Http\Controllers\Admin\Api\SolutionsController;
use App\Http\Controllers\Admin\Api\TagsController;
use App\Http\Controllers\Admin\Api\TechnologiesController;
use App\Http\Controllers\Admin\Api\UsersController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\PreviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'show'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->middleware('throttle:admin-login')->name('login.store');
        Route::get('forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
        Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
        Route::get('reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
        Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
    });

    Route::middleware(['auth', 'active'])->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        /*
        |--------------------------------------------------------------------------
        | Admin API (session-authenticated, CSRF protected, permission gated)
        |--------------------------------------------------------------------------
        */
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('me', [DashboardController::class, 'me'])->name('me');
            Route::get('dashboard', DashboardController::class)->name('dashboard');

            Route::middleware('can:pages.manage')->group(function () {
                Route::get('pages', [PagesController::class, 'index'])->name('pages.index');
                Route::post('pages', [PagesController::class, 'store'])->name('pages.store');
                Route::get('pages/{page}', [PagesController::class, 'show'])->name('pages.show');
                Route::put('pages/{page}', [PagesController::class, 'update'])->name('pages.update');
                Route::delete('pages/{page}', [PagesController::class, 'destroy'])->name('pages.destroy');
                Route::post('pages/{page}/publish', [PagesController::class, 'publish'])->name('pages.publish');
                Route::post('pages/{page}/unpublish', [PagesController::class, 'unpublish'])->name('pages.unpublish');
                Route::get('pages/{page}/versions', [PagesController::class, 'versions'])->name('pages.versions');
                Route::post('pages/{page}/versions/{version}/restore', [PagesController::class, 'restore'])->name('pages.versions.restore');
                Route::put('pages/{page}/sections', [PageSectionsController::class, 'sync'])->name('pages.sections.sync');
            });

            Route::middleware('can:content.manage')->group(function () {
                Route::get('services', [ServicesController::class, 'index'])->name('services.index');
                Route::post('services', [ServicesController::class, 'store'])->name('services.store');
                Route::post('services/reorder', [ServicesController::class, 'reorder'])->name('services.reorder');
                Route::get('services/{service}', [ServicesController::class, 'show'])->name('services.show');
                Route::put('services/{service}', [ServicesController::class, 'update'])->name('services.update');
                Route::delete('services/{service}', [ServicesController::class, 'destroy'])->name('services.destroy');

                Route::get('solutions', [SolutionsController::class, 'index'])->name('solutions.index');
                Route::post('solutions', [SolutionsController::class, 'store'])->name('solutions.store');
                Route::post('solutions/reorder', [SolutionsController::class, 'reorder'])->name('solutions.reorder');
                Route::get('solutions/{solution}', [SolutionsController::class, 'show'])->name('solutions.show');
                Route::put('solutions/{solution}', [SolutionsController::class, 'update'])->name('solutions.update');
                Route::delete('solutions/{solution}', [SolutionsController::class, 'destroy'])->name('solutions.destroy');
                Route::post('solutions/{solution}/screenshots', [SolutionsController::class, 'storeScreenshot'])->name('solutions.screenshots.store');
                Route::put('solutions/{solution}/screenshots', [SolutionsController::class, 'syncScreenshots'])->name('solutions.screenshots.sync');

                Route::get('technologies', [TechnologiesController::class, 'index'])->name('technologies.index');
                Route::post('technologies', [TechnologiesController::class, 'store'])->name('technologies.store');
                Route::delete('technologies/{technology}', [TechnologiesController::class, 'destroy'])->name('technologies.destroy');
            });

            Route::middleware('can:articles.manage')->group(function () {
                Route::get('articles', [ArticlesController::class, 'index'])->name('articles.index');
                Route::post('articles', [ArticlesController::class, 'store'])->name('articles.store');
                Route::get('articles/{article}', [ArticlesController::class, 'show'])->name('articles.show');
                Route::put('articles/{article}', [ArticlesController::class, 'update'])->name('articles.update');
                Route::delete('articles/{article}', [ArticlesController::class, 'destroy'])->name('articles.destroy');
                Route::put('articles/{article}/placements', [PlacementsController::class, 'sync'])->name('articles.placements.sync');

                Route::get('categories', [CategoriesController::class, 'index'])->name('categories.index');
                Route::post('categories', [CategoriesController::class, 'store'])->name('categories.store');
                Route::put('categories/{category}', [CategoriesController::class, 'update'])->name('categories.update');
                Route::delete('categories/{category}', [CategoriesController::class, 'destroy'])->name('categories.destroy');

                Route::get('tags', [TagsController::class, 'index'])->name('tags.index');
                Route::post('tags', [TagsController::class, 'store'])->name('tags.store');
                Route::put('tags/{tag}', [TagsController::class, 'update'])->name('tags.update');
                Route::delete('tags/{tag}', [TagsController::class, 'destroy'])->name('tags.destroy');
            });

            Route::middleware('can:careers.manage')->group(function () {
                Route::get('jobs', [JobsController::class, 'index'])->name('jobs.index');
                Route::post('jobs', [JobsController::class, 'store'])->name('jobs.store');
                Route::get('jobs/{job}', [JobsController::class, 'show'])->name('jobs.show');
                Route::put('jobs/{job}', [JobsController::class, 'update'])->name('jobs.update');
                Route::delete('jobs/{job}', [JobsController::class, 'destroy'])->name('jobs.destroy');
            });

            Route::middleware('can:applications.view')->group(function () {
                Route::get('applications', [ApplicationsController::class, 'index'])->name('applications.index');
                Route::get('applications-export', [ApplicationsController::class, 'export'])->name('applications.export');
                Route::get('applications/{application}', [ApplicationsController::class, 'show'])->name('applications.show');
                Route::patch('applications/{application}', [ApplicationsController::class, 'update'])->name('applications.update');
                Route::delete('applications/{application}', [ApplicationsController::class, 'destroy'])->name('applications.destroy');
                Route::post('applications/{application}/notes', [ApplicationsController::class, 'storeNote'])->name('applications.notes.store');
                Route::get('applications/{application}/cv', [ApplicationsController::class, 'downloadCv'])->name('applications.cv');
            });

            Route::middleware('can:media.manage')->group(function () {
                Route::get('media', [MediaController::class, 'index'])->name('media.index');
                Route::post('media', [MediaController::class, 'store'])->name('media.store');
                Route::put('media/{medium}', [MediaController::class, 'update'])->name('media.update');
                Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');
            });

            Route::middleware('can:navigation.manage')->group(function () {
                Route::get('navigation', [NavigationController::class, 'index'])->name('navigation.index');
                Route::put('navigation/{menu}', [NavigationController::class, 'update'])->name('navigation.update');
            });

            Route::middleware('can:settings.manage')->group(function () {
                Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
                Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
            });

            Route::middleware('can:users.manage')->group(function () {
                Route::get('users', [UsersController::class, 'index'])->name('users.index');
                Route::post('users', [UsersController::class, 'store'])->name('users.store');
                Route::put('users/{user}', [UsersController::class, 'update'])->name('users.update');
                Route::delete('users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
                Route::get('roles', [RolesController::class, 'index'])->name('roles.index');
                Route::put('roles/{role}', [RolesController::class, 'update'])->name('roles.update');
            });

            Route::middleware('can:enquiries.view')->group(function () {
                Route::get('enquiries', [EnquiriesController::class, 'index'])->name('enquiries.index');
                Route::get('enquiries/{enquiry}', [EnquiriesController::class, 'show'])->name('enquiries.show');
                Route::patch('enquiries/{enquiry}', [EnquiriesController::class, 'update'])->name('enquiries.update');
                Route::delete('enquiries/{enquiry}', [EnquiriesController::class, 'destroy'])->name('enquiries.destroy');
            });

            Route::get('audit-logs', AuditLogsController::class)
                ->middleware('can:audit.view')
                ->name('audit-logs.index');
        });

        Route::get('pages/{page}/preview', PreviewController::class)
            ->middleware('can:pages.manage')
            ->name('pages.preview');

        // React admin shell — must be last so it does not shadow the API.
        Route::get('/{any?}', AdminShellController::class)->where('any', '.*')->name('shell');
    });
});
