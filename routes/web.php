<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/collabora', [\App\Http\Controllers\CollaboraController::class, 'index']);

Route::prefix('wopi')->group(function () {
    Route::get('files/{id}/contents', [\App\Http\Controllers\WopiController::class, 'wopiGetFile']);
    Route::post('files/{id}/contents', [\App\Http\Controllers\WopiController::class, 'wopiPutFile'])
        ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
//    Route::any('files/{id}/contents', [\App\Http\Controllers\WopiController::class, 'parseWopiRequest'])
//        ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::get('files/{id}', [\App\Http\Controllers\WopiController::class, 'wopiCheckFileInfo']);
});
