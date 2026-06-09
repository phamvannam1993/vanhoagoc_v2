<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * List of services that need to be registered.
     *
     * @var array
     */
    private $repositoriesToRegister = [
        'Base',
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerRepositories();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register the repositories from the specified list.
     */
    private function registerRepositories(): void
    {
        foreach ($this->repositoriesToRegister as $repository) {
            $interface = "App\Repositories\Interfaces\\{$repository}RepositoryInterface";
            $implementation = "App\Repositories\\{$repository}Repository";
            $this->app->bind($interface, $implementation);
        }
    }
}
