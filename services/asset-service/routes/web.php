<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('dashboard'));
Route::get('/actifs', fn () => view('actifs'));
Route::get('/actifs/{id}', fn () => view('actif-detail'));
Route::get('/maintenances', fn () => view('maintenances'));
Route::get('/rapports', fn () => view('rapports'));
Route::get('/utilisateurs', fn () => view('utilisateurs'));
Route::get('/login', fn () => view('login'))->name('login');
