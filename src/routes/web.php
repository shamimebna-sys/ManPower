<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*Route::get('/', function () {
   return redirect('/panel/profile');
});
*/
Route::get('/.lsrecap/recaptcha', function () {
   return redirect('/');
});
Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');

    echo 'clear';
});


Route::any('/', [\App\Http\Controllers\PageController::class, 'home'])->name('web.home');
Route::any('/about', [\App\Http\Controllers\PageController::class, 'about'])->name('web.about');
Route::any('/faq', [\App\Http\Controllers\PageController::class, 'faq'])->name('web.faq');
Route::any('/contact', [\App\Http\Controllers\PageController::class, 'contact'])->name('web.contact');
Route::any('/down', [\App\Http\Controllers\PageController::class, 'down'])->name('web.down');

Route::any('/candidates/{id?}', [\App\Http\Controllers\PageController::class, 'candidates'])->name('web.candidates');
Route::any('/employers/{id?}', [\App\Http\Controllers\PageController::class, 'employers'])->name('web.employers');

Route::any('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('web.login');
Route::any('/registration/{form?}', [\App\Http\Controllers\AuthController::class, 'registration'])->name('web.registration');

Route::group(['prefix' => 'my'], function () {
    Route::any('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('web.logout');
    Route::any('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('web.dashboard');

});

Route::group(['prefix'=>'ajax','as'=>'web.ajax.'], function(){
    Route::group(['prefix'=>'employer-candidate'], function() {
        Route::post('/add-remove', [\App\Http\Controllers\Admin\VoyagerAjaxController::class,'employerCandidateAddRemove'])->name('employer-candidate-add-remove');
    });
});
