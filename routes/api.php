<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\UserController;
use App\Jobs\GitPull;
use Illuminate\Support\Facades\Log;
use OpenSpout\Common\Entity\Row;

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

//staff login
Route::post('/staffLogin',[StaffController::class, 'login']);

//register
Route::post('/register',[ApiController::class, 'register']);

//forget password
Route::post('/forgetPassword',[ApiController::class, 'forgetPassword']);

//register member
Route::any('/registerMember/{code}',[ApiController::class, 'registerMember'])->name('registerMember');

Route::post('/get2Dpics',[ApiController::class, 'get2Dpics']);
Route::post('/set2DpicFinish',[ApiController::class, 'set2DpicFinish']);

// get videos
Route::post('/getVideos',[ApiController::class, 'getVideos']);
Route::post('/setVideoFinish',[ApiController::class, 'setVideoFinish']);


Route::post('/guestLogin',[UserController::class, 'guestLogin']);





Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::get('/user',[UserController::class, 'profile']);

    //staff only routes
    Route::group(['middleware'=>['admin']],function(){
        //addOrderList
        Route::post('/addOrderList',[StaffController::class, 'addOrderList']);

        //add video
        Route::post('/addVideo',[StaffController::class, 'addVideo']);

         //video failed
        Route::post('/videoFailed/{media}',[ApiController::class, 'videoFailed']);

        // finish media
        Route::post('/finishMedia',[ApiController::class, 'set2DpicFinish']);

        //query all orders
        Route::post('/queryAllOrderList',[StaffController::class, 'queryAllOrderList']);

        Route::post('/uploadVideo/{media}',[StaffController::class, 'uploadVideo']);
    });


     //updateMemberName
     Route::post('/updateMemberName',[ApiController::class, 'updateMemberName']);

     //updatePassword
     Route::post('/updatePassword',[ApiController::class, 'updatePassword']);

     //userUpdate
     Route::put('/userUpdate',[ApiController::class, 'userUpdate']);

     //queryOrderList
     Route::post('/queryOrderList',[ApiController::class, 'queryOrderList']);

     //updateVideoName
     Route::post('/updateVideoName/{id}',[ApiController::class, 'updateVideoName']);

     //resend confirmation mail
    Route::post('/sendConfirmEmail',[ApiController::class, 'sendConfirmEmail']);

    //upload picture from frontend
    Route::post('/uploadPicture',[ApiController::class, 'uploadPicture']);

    //upload cropped pics from frontend
    Route::post('/uploadCanvas',[ApiController::class, 'uploadCanvas']);

    //upload video from frontend
    Route::post('/uploadVideo',[ApiController::class, 'uploadVideo']);

    //get user orders
    Route::get('/orders',[ApiController::class, 'orders']);

    //get user videos
    Route::get('/videos',[ApiController::class, 'videos']);

    //get single video
    Route::post('/media/{media}',[ApiController::class, 'video']);

    //delete user video
    Route::delete('/deleteVideo/{media}',[ApiController::class, 'deleteVideo']);

    //add user to device
    Route::post('/addDevice',[UserController::class, 'addDevice']);

});

//webhook
Route::post('/github',function(){

    Log::info('webhook triggered');
    GitPull::dispatch();
});
