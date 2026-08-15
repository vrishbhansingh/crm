<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Order;
use App\Models\PaymentDetails;
use App\Models\Pipeline;
use App\Models\ProjectInfo;
use App\Models\Task;
use App\Models\User;
use App\Observers\CrmAuditObserver;
use App\Tenancy\TenantConnectionManager;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantConnectionManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        foreach ([Company::class, Contact::class, Deal::class, Lead::class, Order::class, PaymentDetails::class, Pipeline::class, ProjectInfo::class, Task::class] as $model) {
            $model::observe(CrmAuditObserver::class);
        }

        if (config('tenancy.mode') === 'shared') {
            User::observe(CrmAuditObserver::class);
        }
    }
}
