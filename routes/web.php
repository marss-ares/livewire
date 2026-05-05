<?php

use App\Livewire\RBSMaterials\Roles\RolesIndex;
use App\Livewire\RBSMaterials\Users\UsersIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('/users', UsersIndex::class)->name('users.index');

    Route::get('/roles', RolesIndex::class)
        ->middleware('permission:roles.view')
        ->name('roles.index');
});

require __DIR__.'/settings.php';
