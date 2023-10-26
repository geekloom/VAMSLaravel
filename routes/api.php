<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\apiController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/testFuntion',[apiController::class, 'test']);


/*
* ------------------------------------------------------------------------
* VAMS Api Version 1 (v1)
* ------------------------------------------------------------------------
*/



Route::post('/v1/userAuthentication',[apiController::class, 'authentication']);
Route::post('/v1/getUserData',[apiController::class, 'userDatas']);
Route::post('/v1/createNewUser',[apiController::class, 'createNewUser']);
Route::any('/v1/resetPassword',[apiController::class, 'resetPassword']);
Route::any('/v1/checkUserType',[apiController::class, 'checkUsersType']);
Route::any('/v1/createStaff',[apiController::class, 'createStaff']);











