<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MainController;



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

//

Route::post("field/create", [MainController::class, "CreateField"]);
Route::get("field/list", [MainController::class, "ListField"]);

Route::post("animal/create-one", [MainController::class, "CreateOneAnimal"]);
Route::post("animal/create-random", [MainController::class, "CreateRandomAnimals"]);

Route::get("update", [MainController::class, "Update"]);
Route::get("battlefield", [MainController::class, "Battlefield"]);

Route::get("showme", [MainController::class, "ShowMe"]);
