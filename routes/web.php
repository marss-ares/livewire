<?php

use App\Http\Controllers\FormExportController;
use App\Livewire\RBSMaterials\Forms\FormsIndex;
use App\Livewire\RBSMaterials\Forms\FormEdit;
use App\Livewire\RBSMaterials\Forms\FormEntries;
use App\Livewire\RBSMaterials\Roles\RolesIndex;
use App\Livewire\RBSMaterials\Statuses\StatusesIndex;
use App\Livewire\RBSMaterials\Users\UsersIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('/users', UsersIndex::class)->middleware('permission:users.view')->name('users.index');

    Route::get('/roles', RolesIndex::class)
        ->middleware('permission:roles.view')
        ->name('roles.index');

    Route::get('/forms', FormsIndex::class)->name('forms.index');
    Route::get('/forms/{form}/edit', FormEdit::class)->name('forms.edit');
    Route::get('/forms/{form}/entries', FormEntries::class)->name('forms.entries');

    Route::get('/statuses', StatusesIndex::class)->name('statuses.index');

    Route::get('/forms/{form}/export', FormExportController::class)->name('forms.export');
});

require __DIR__.'/settings.php';
