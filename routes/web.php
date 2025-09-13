
<?php

use Illuminate\Support\Facades\Route;

// Swagger UI route
Route::get('/swagger', function () {
    return redirect('/swagger-ui/index.html');
});

Route::get('/', function () {
    return view('welcome');
});

