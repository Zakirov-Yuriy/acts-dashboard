<?php

use Illuminate\Support\Facades\Route;

// Всё, что не /api/*, отдаём SPA. Клиентский роутинг (vue-router) разрулит путь.
Route::view('/{any?}', 'app')->where('any', '^(?!api).*$');
