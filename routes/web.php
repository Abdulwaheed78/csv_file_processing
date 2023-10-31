<?php

use App\Http\Controllers\CsvController;
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


Route::get('/', [CsvController::class, 'index'])->name('upload-csv');
Route::post('/', [CsvController::class, 'upload'])->name('upload-csv');

Route::post('/process', [CsvController::class, 'process'])->name('process-csv');

Route::get('/show-csv-content/{fileName}',[CsvController::class,'showCsvContent'])->name('show-csv-content');

Route::post('/prepare-csv', [CsvController::class, 'prepareCsv'])->name('prepare-csv');
Route::get('/download-prepared-csv/{filename}', [CsvController::class, 'downloadPreparedCsv'])->name('download-prepared-csv');
Route::get('/prev',[CsvController::class,'prev'])->name('prev');
Route::get('/clean',[CsvController::class,'clean'])->name('clean');
Route::post('/clean',[CsvController::class,'clean'])->name('clean');
Route::get('/delete-file/{filename}', [CsvController::class,'deleteFile'])->name('deleteFile');
