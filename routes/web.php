<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\RBSMaterials\Users\UsersIndex;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('/users', UsersIndex::class)->name('users.index');
});

require __DIR__.'/settings.php';
