<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('dashboard'));
Route::get('/login', fn () => view('login'))->name('login');
