<?php

namespace Modules\Enrollment\Providers;

use Illuminate\Support\ServiceProvider;

class EnrollmentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register view namespace
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'enrollment');
    }

    public function register()
    {
        //
    }
}