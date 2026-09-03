<?php

use Illuminate\Support\Facades\Route;

Route::get('exam-results/export-pdf', [\App\Http\Controllers\Admin\VoyagerExamResultController::class, 'exportPdf'])->name('voyager.exam-results.export-pdf');

Voyager::routes();


Route::any('/registration', [\App\Http\Controllers\Admin\AuthenticationController::class, 'registration'])->name('voyager.registration');
Route::any('/registration-success', [\App\Http\Controllers\Admin\AuthenticationController::class, 'registrationSuccess'])->name('voyager.registration');
Route::any('/forgot', [\App\Http\Controllers\Admin\AuthenticationController::class, 'forgot'])->name('voyager.forgot');


Route::get('admin/users/relation', [\App\Http\Controllers\Admin\VoyagerUserController::class, 'relation'])->name('voyager.users.relation');

Route::group(['prefix'=>'reports/generate','as'=>'admin.reports.generate.'], function(){
    Route::post('/agent-ledger', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'agentLedger'])->name('agent-ledger');
    Route::post('/agent-report', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'agentReport'])->name('agent-report');
    Route::post('/selected-candidate-report', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'selectedCandidateReport'])->name('selected-candidate-report');
    Route::post('/flight-report', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'flightReport'])->name('flight-report');
    Route::post('/flight-schedule', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'flightSchedule'])->name('flight-schedule');
    Route::post('/employee-report', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'employerReport'])->name('employee-report');
    Route::post('/exam-report', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'examReport'])->name('exam-report');
    Route::post('/teacher-schedule', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'teacherSchedule'])->name('teacher-schedule');
    Route::post('/exam-result-sheet', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'examResultSheet'])->name('exam-result-sheet');
    Route::post('/candidate-resume-report', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'candidateResumeReport'])->name('candidate-resume-report');
    Route::post('/candidate-list-report', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'candidateListReport'])->name('candidate-list-report');
    Route::post('/company-report', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'companyReport'])->name('company-report');
    Route::post('/police-clearance-report', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'policeClearanceReport'])->name('police-clearance-report');
    Route::post('/arc-report', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'arcReport'])->name('arc-report');
    Route::post('/visa-report', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'visaReport'])->name('visa-report');
    Route::post('/tickets-report', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'ticketsReport'])->name('tickets-report');
    Route::post('/money-receipt-report', [\App\Http\Controllers\Admin\VoyagerReportController::class, 'moneyReceiptReport'])->name('money-receipt-report');

    Route::any('/invoice/{id}', [\App\Http\Controllers\Admin\VoyagerInvoiceController::class, 'printInvoice'])->name('invoice-report');
    Route::any('/invoice-money-receipts/{id}', [\App\Http\Controllers\Admin\VoyagerInvoiceMoneyReceiptController::class, 'printInvoiceMoneyReceipt'])->name('invoice-money-receipts-report');

    Route::any('/ticket-invoice/{id}', [\App\Http\Controllers\Admin\VoyagerTicketInvoiceController::class, 'printInvoice'])->name('ticket-invoice-report');
    Route::any('/ticket-invoice-money-receipts/{id}', [\App\Http\Controllers\Admin\VoyagerTicketInvoiceMoneyReceiptController::class, 'printInvoiceMoneyReceipt'])->name('ticket-invoice-money-receipts-report');


});

Route::group(['prefix'=>'ajax','as'=>'admin.ajax.'], function(){
    Route::get('/class-group-candidates/{id}', [\App\Http\Controllers\Admin\VoyagerAjaxController::class, 'classGroupCandidates'])->name('class-group-candidates');
    Route::any('/notifications', [\App\Http\Controllers\Admin\VoyagerAjaxController::class,'notifications'])->name('notifications');
    Route::any('/notification-counter', [\App\Http\Controllers\Admin\VoyagerAjaxController::class,'notificationCounter'])->name('notification-counter');
    Route::any('/payment-approval/{id}', [\App\Http\Controllers\Admin\VoyagerAjaxController::class,'paymentApproval'])->name('payment-approval');
    Route::any('/candidate-approval/{id}', [\App\Http\Controllers\Admin\VoyagerAjaxController::class,'candidateApproval'])->name('candidate-approval');
    Route::any('/candidate-payment-approval/{id}', [\App\Http\Controllers\Admin\VoyagerAjaxController::class,'candidatePaymentApproval'])->name('candidate-payment-approval');
    Route::get('/search-candidates', [\App\Http\Controllers\Admin\VoyagerCandidatesController::class, 'searchCandidates'])->name('search-candidates');

    Route::group(['prefix'=>'employer-candidate'], function() {
        Route::post('/add-remove', [\App\Http\Controllers\Admin\VoyagerAjaxController::class,'employerCandidateAddRemove'])->name('employer-candidate-add-remove');
    });
});


Route::group(['prefix'=>'my','as'=>'admin.my.'], function(){
    Route::group(['prefix'=>'profile'], function() {
        Route::get('/', [\App\Http\Controllers\Admin\VoyagerMyPanelController::class, 'profile'])->name('profile.index');
        Route::get('/profile-edit', [\App\Http\Controllers\Admin\VoyagerMyPanelController::class, 'profileEdit'])->name('profile.edit');
        Route::put('/update', [\App\Http\Controllers\Admin\VoyagerMyPanelController::class, 'profileUpdate'])->name('profile.update');
        Route::get('/export', [\App\Http\Controllers\Admin\VoyagerMyPanelController::class, 'profileExport'])->name('profile-export');

    });
    Route::group(['prefix'=>'wallet'], function() {
        Route::get('/', [\App\Http\Controllers\Admin\VoyagerMyPanelController::class, 'wallet'])->name('wallet.index');
        Route::post('/store', [\App\Http\Controllers\Admin\VoyagerMyPanelController::class, 'walletStore'])->name('wallet.store');
    });

    Route::group(['prefix'=>'candidate'], function() {
        Route::get('/', [\App\Http\Controllers\Admin\VoyagerMyPanelController::class, 'candidate'])->name('candidate.index');
        Route::get('/create', [\App\Http\Controllers\Admin\VoyagerMyPanelController::class, 'candidateCreate'])->name('candidate.create');
        Route::post('/store', [\App\Http\Controllers\Admin\VoyagerMyPanelController::class, 'candidateStore'])->name('candidate.store');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\VoyagerMyPanelController::class, 'candidateEdit'])->name('candidate.edit');
        Route::put('/{id}/update', [\App\Http\Controllers\Admin\VoyagerMyPanelController::class, 'candidateUpdate'])->name('candidate.update');
    });

    Route::group(['prefix'=>'payment-request'], function() {
        Route::get('/', [\App\Http\Controllers\Admin\VoyagerMyPanelController::class, 'paymentRequest'])->name('payment-request.index');
    });
});



Route::group(['prefix'=>'profile-edit','as'=>'admin.profile.'], function(){
    Route::any('/', [\App\Http\Controllers\Admin\VoyagerProfileController::class,'index'])->name('edit');
});


