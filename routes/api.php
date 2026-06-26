<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ActController;
use App\Http\Controllers\Api\ReferenceController;

// Сводка по дашборду (учитывает активные фильтры)
Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

// Оплаты: список с фильтрами + вычисленным статусом акта
Route::get('/payments', [PaymentController::class, 'index']);

// Проекты: список с агрегатами (суммы, кол-во оплат, закрытые/открытые акты)
Route::get('/projects', [ProjectController::class, 'index']);

// Управление актами: пометить отправленным / подписанным / комментарий
Route::patch('/acts/{act}', [ActController::class, 'update']);

// Справочники для фильтров (юрлица, проекты, этапы услуг, статусы)
Route::get('/references', [ReferenceController::class, 'index']);
