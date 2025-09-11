<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ReferralsIndex;
use App\Livewire\ReferralWorkflowShow;
use App\Livewire\Dashboard;
use App\Http\Controllers\OcrMedExtractController;
use App\Livewire\Referrals\OrthoIntakeForm;
// routes/web.php
use App\Livewire\OcrTestPage;

Route::middleware(['web','auth','verified'])->group(function () {
    Route::get('/ocr-test', OcrTestPage::class)->name('ocr.test');
});

Route::get('/referrals/ortho-intake', OrthoIntakeForm::class)
    ->name('referrals.ortho.intake');
// routes/web.php




Route::get('/dashboard', function() {
    return view('dashboard-page');
})->name('dashboard');

// routes/web.php
Route::get(
    '/referrals/{referral}/steps/{step}/pdf',
    \App\Http\Controllers\ReferralFormPdfController::class
)->name('referral.step.pdf');


Route::post('/signature-upload', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'data_url' => 'required|string|starts_with:data:image/png;base64,',
    ]);

    $png = base64_decode(substr($request->data_url, strpos($request->data_url, ',') + 1));

    $name = 'signatures/'.uniqid().'.png';
    \Illuminate\Support\Facades\Storage::disk('public')->put($name, $png);

    return response()->json(['path' => $name]);   // e.g. signatures/abc123.png
})->name('signature.upload');


Route::post('/ocr/med-extract', [OcrMedExtractController::class, 'extract'])->name('ocr.extract');


// Redirect root route to login
Route::redirect('/', '/login');

// Protected Routes (Only accessible by authenticated users)
Route::middleware(['auth', 'verified'])->group(function () {

    Route::view('/med-reconciliation', 'med-reconciliation')->name('med-reconciliation');

    Route::get('/patient-timeline', function () {
        return view('patient-timeline');
    })->name('patient-timeline');
    Route::get('/patient-view', function () {
        return view('patient-view');
    })->name('patient-view');    
	Route::get('/visit', function () {
        return view('visit');
    })->name('visit');

    Route::view('/discharges', 'referrals.index')->name('referrals.index');

    Route::get('/discharges/{id}/workflow', function ($id) {
        return view('referrals.workflow-show', ['id' => $id]);
    })->name('referrals.workflow.show');


});

// Jetstream / Sanctum Auth Routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Additional Jetstream-specific routes if needed
});
