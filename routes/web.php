<?php

use Illuminate\Support\Facades\Route;

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
http://127.0.0.1:8000/storage/photos/Dha2XphVPSc6cbKLkpzFvr9ZiHaQTQIS7P2I4FFR.png
Route::get('/', function () {
    return view('welcome');
});
Route::get('/storage/photos/{filename}', function ($filename) {
    $path = storage_path( 'app/public/photos/' . $filename);
  
    if (!file_exists($path)) {
        abort(404);
    }

    $file = file_get_contents($path);
    
    return response($file, 200)->header('Content-Type', 'image/jpeg');
});
