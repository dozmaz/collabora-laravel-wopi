<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function (){
    return view('welcome');
});

Route::get('/{token_correspondencia}', [\App\Http\Controllers\CollaboraController::class, 'welcome']);

Route::prefix('collabora')->group(function () {
    Route::get('/{token_correspondencia}', [\App\Http\Controllers\CollaboraController::class, 'index']);
    Route::post('/cargararchivo', [\App\Http\Controllers\CollaboraController::class, 'cargararchivo']);
    Route::post('/borrarcontenido', [\App\Http\Controllers\CollaboraController::class, 'borrarcontenido']);
    Route::post('/exportarpdf', [\App\Http\Controllers\CollaboraController::class, 'exportarpdf']);
});
Route::prefix('wopi')->group(function () {
    Route::get('files/{id}/{usuarioEdicionId}/contents', [\App\Http\Controllers\WopiController::class, 'wopiGetFile']);
    Route::post('files/{id}/{usuarioEdicionId}/contents', [\App\Http\Controllers\WopiController::class, 'wopiPutFile'])
        ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::get('files/{id}/{usuarioEdicionId}', [\App\Http\Controllers\WopiController::class, 'wopiCheckFileInfo']);
});
