<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\IdeaController;
use App\Http\Controllers\API\CommentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['api'])->group(function () {
  Route::prefix('/idea')->group(function () {
    Route::get('', [IdeaController::class, 'index']);
    Route::get('/{id}', [IdeaController::class, 'show']);
    Route::post('', [IdeaController::class, 'store']);
    Route::put('/{idea}', [IdeaController::class, 'update']);
    Route::delete('/{idea}', [IdeaController::class, 'delete']);
  });
  Route::prefix('/comment')->group(function () {
    Route::get('', [CommentController::class, 'index']);
    Route::get('/{id}', [CommentController::class, 'show']);
    Route::post('', [CommentController::class, 'store']);
    Route::put('/{comment}', [CommentController::class, 'update']);
    Route::delete('/{comment}', [CommentController::class, 'delete']);
  });
});
