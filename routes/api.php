<?php

use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group(["prefix"=>"v1","namespace"=>"App\Http\Controllers\Api\V1"],function(){
    Route::controller(UserController::class)->prefix("admin")->group( function(){
        Route::post("login","userLogin");
        Route::post('signup',"userSignUp");
        Route::get("all-Products","getAllProducts");
        Route::get("all-Categories","getAllCategories");
        Route::get("product/{id}",'getSingleProduct');
        
      });
      Route::controller(UserController::class)->prefix("admin")->group( function(){
        Route::get("userInfo","userInfo");
        Route::post("product","prodctNew");
        Route::delete('product/{id}', 'productDelete');
        Route::post('MuliProductsDelete', 'multiProductDelete');
        Route::post('product/{id}', 'productUpdate');

        
      });
    
});
