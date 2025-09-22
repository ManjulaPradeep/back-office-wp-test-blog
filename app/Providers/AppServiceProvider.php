<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->make(\Illuminate\Contracts\Events\Dispatcher::class)
            ->listen(
                SocialiteWasCalled::class,
                'SocialiteProviders\WordPress\WordPressExtendSocialite@handle'
            );
    }
}
