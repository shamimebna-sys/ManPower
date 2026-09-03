<?php

namespace App\Providers;

use App\FormFields\EmailFromField;
use App\FormFields\LinkFromField;
use App\FormFields\MobileFromField;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use TCG\Voyager\Facades\Voyager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        Schema::defaultStringLength(191);

        //form field
        Voyager::addFormField(EmailFromField::class);
        Voyager::addFormField(MobileFromField::class);
        Voyager::addFormField(LinkFromField::class);

        Voyager::addAction(\App\Actions\ClassScheduleAction::class);
        Voyager::addAction(\App\Actions\AgentPaymentAction::class);
//        Voyager::addAction(\App\Actions\TeacherPaymentAction::class);
        Voyager::addAction(\App\Actions\PaymentApprovalAction::class);
        Voyager::addAction(\App\Actions\ExamResultPublishAction::class);
        Voyager::addAction(\App\Actions\CandidateExamResultPublishAction::class);
        View::composer('voyager::*', function ($view) {
            $view->getFactory()->flushFinderCache();
        });
    }
}
