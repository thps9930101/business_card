<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

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

//login
Route::post('/login',[ApiController::class, 'login']);
//register
Route::post('/register',[ApiController::class, 'register']);

//forget password
Route::post('/forgetPassword',[ApiController::class, 'forgetPassword']);

//register member
Route::any('/registerMember/{code}',[ApiController::class, 'registerMember'])->name('registerMember');

Route::post('/get2Dpics',[ApiController::class, 'get2Dpics']);
Route::post('/set2DpicFinish',[ApiController::class, 'set2DpicFinish']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();

});

Route::group(['middleware' => ['auth:sanctum']], function () {
     //updateMemberName
     Route::post('/updateMemberName',[ApiController::class, 'updateMemberName']);

     //updatePassword
     Route::post('/updatePassword',[ApiController::class, 'updatePassword']);

     //userUpdate
     Route::put('/userUpdate',[ApiController::class, 'userUpdate']);

     //queryOrderList
     Route::post('/queryOrderList',[ApiController::class, 'orders']);

     //updateVideoName
     Route::post('/updateVideoName/{id}',[ApiController::class, 'updateVideoName']);

     //resend confirmation mail
    Route::post('/sendConfirmEmail',[ApiController::class, 'sendConfirmEmail']);

    //upload picture from frontend
    Route::post('/uploadPicture',[ApiController::class, 'uploadPicture']);

    //get user orders
    Route::get('/orders',[ApiController::class, 'orders']);

    //get user videos
    Route::get('/videos',[ApiController::class, 'videos']);
});
