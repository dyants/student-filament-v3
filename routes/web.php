<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Tambahkan route untuk generate invoice PDF
Route::get ('{student}/invoice/generate', [App\Http\Controllers\InvoicesController::class, 'generatePdf'])->name('students.invoice.generate');