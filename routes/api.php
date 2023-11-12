<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MpesaController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/confirmtrans',[MpesaController::class, 'confirmpay']);
Route::post('/confirmtrans',[MpesaController::class, 'confirmpay']);



Route::get('/registerUrl', [MpesaController::class, 'registerUrl']);
Route::get('/stkpush', [MpesaController::class, 'stkPushRequest']);

Route::get('/valtrans', [MpesaController::class, 'valtrans']);
Route::post('/valtrans', [MpesaController::class, 'valtrans']);

Route::get('/simulatePaybill', [MpesaController::class, 'simulatePaybill']);
Route::get('/registerCallBacks', [MpesaController::class, 'registercallbacks']);

Route::get('/getPassword', [MpesaController::class, 'getPassword']);
Route::get('/mob-mon-cb', [MpesaController::class, 'mpesaResponse']);
Route::post('/mob-mon-cb', [MpesaController::class, 'mpesaResponse']);
