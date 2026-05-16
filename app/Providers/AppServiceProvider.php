<?php

namespace App\Providers;

use App\Core\Application\Contact\Interfaces\ContactRepositoryInterface;
use App\Core\Application\Contact\UseCases\ContactUseCase;
use App\Repositories\ContactRepository;
use App\Services\Contact\ContactServiceImpl;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);
        $this->app->bind(ContactUseCase::class, ContactServiceImpl::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
