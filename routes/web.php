<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\movieController;

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

Route::post('/upload-chunk', [movieController::class, 'uploadChunk'])->name('upload.chunk');
Route::post('/upload-complete', [movieController::class, 'completeUpload'])->name('upload.complete');


Route::get('/upload', [MovieController::class, 'index']);
Route::post('/upload-chunk', [MovieController::class, 'uploadChunk']);
Route::post('/upload-complete', [MovieController::class, 'completeUpload']);
Route::get('/', function () {
    return view('welcome');
});
