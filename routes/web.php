<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MpesaController;

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


Route::get('/confirmtrans',[MpesaController::class, 'confirmpay']);
Route::post('/confirmtrans',[MpesaController::class, 'confirmpay']);



Route::get('/sayHello', [MpesaController::class, 'sayHello']);
Route::get('/registerUrl', [MpesaController::class, 'registerUrl']);
Route::get('/stkpush', [MpesaController::class, 'stkPushRequest']);

Route::get('/valtrans', [MpesaController::class, 'valtrans']);

Route::get('/simulatePaybill', [MpesaController::class, 'simulatePaybill']);


Route::get('/registerCallBacks', [MpesaController::class, 'registercallbacks']);

Route::get('/savanastkpush', [MpesaController::class, 'savanabitsStkPush']);
Route::get('/getPassword', [MpesaController::class, 'getPassword']);
Route::get('/mob-mon-cb', [MpesaController::class, 'mpesaResponse']);
Route::post('/mob-mon-cb', [MpesaController::class, 'mpesaResponse']);
