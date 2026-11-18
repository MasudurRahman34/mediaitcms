<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Job;
use App\Models\NavigationItem;
use App\Models\NavigationMenu;
use App\Models\Page;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Solution;
use App\Models\User;
use App\Observers\SitemapObserver;
use App\Services\NavigationRepository;
use App\Services\Seo\SitemapBuilder;
use App\Services\SettingsRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsRepository::class);
        $this->app->singleton(NavigationRepository::class);
        $this->app->singleton(SitemapBuilder::class);
    }

    public function boot(): void
    {
        $this->registerGates();
        $this->registerRateLimiters();
        $this->registerBladeDirectives();
        $this->registerViewComposers();
        $this->registerObservers();
    }

    private function registerGates(): void
    {
        Gate::before(function (User $user) {
            return $user->isSuperAdmin() ? true : null;
        });

        foreach ([
            'pages.manage',
            'content.manage',
            'articles.manage',
            'careers.manage',
            'applications.view',
            'media.manage',
            'navigation.manage',
            'settings.manage',
            'users.manage',
            'enquiries.view',
            'audit.view',
        ] as $permission) {
            Gate::define($permission, fn (User $user) => $user->hasPermission($permission));
        }
    }

    private function registerRateLimiters(): void
    {
        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('apply', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));
        RateLimiter::for('admin-login', fn (Request $request) => [
            Limit::perMinute(5)->by($request->input('email').'|'.$request->ip()),
            Limit::perMinute(20)->by($request->ip()),
        ]);
    }

    private function registerBladeDirectives(): void
    {
        Blade::if('permission', fn (string $permission) => auth()->check() && auth()->user()->hasPermission($permission));

        Blade::directive('richtext', function (string $expression) {
            return "<?php echo \\App\\Support\\RichText::clean($expression); ?>";
        });

        Blade::directive('plain', function (string $expression) {
            return "<?php echo e(\\App\\Support\\RichText::plain($expression)); ?>";
        });
    }

    /**
     * Share settings + navigation with every view so components and partials
     * (including <x-site.*> components) always have them available.
     */
    private function registerViewComposers(): void
    {
        View::composer('*', function ($view) {
            $view->with('settings', app(SettingsRepository::class));
            $view->with('navigation', app(NavigationRepository::class));
        });
    }

    private function registerObservers(): void
    {
        $observer = SitemapObserver::class;

        foreach ([Page::class, Service::class, Solution::class, Article::class, Job::class] as $model) {
            $model::observe($observer);
        }

        // Settings and navigation changes affect cached markup.
        Setting::saved(fn () => app(SettingsRepository::class)->flush());
        Setting::deleted(fn () => app(SettingsRepository::class)->flush());
        NavigationMenu::saved(fn () => app(NavigationRepository::class)->flush());
        NavigationItem::saved(fn () => app(NavigationRepository::class)->flush());
        NavigationItem::deleted(fn () => app(NavigationRepository::class)->flush());
    }
}
