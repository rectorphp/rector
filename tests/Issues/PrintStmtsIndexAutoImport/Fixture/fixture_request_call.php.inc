<?php

use Illuminate\Http\Request;

Route::middleware('something')->group(function () {
    Route::get('/some', 'SomeController@some');
    Route::get('/some/{id?}', 'SomeController@some');
});

\Request::createFromBase($symfonyRequest);

?>
-----
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware('something')->group(function () {
    Route::get('/some', 'SomeController@some');
    Route::get('/some/{id?}', 'SomeController@some');
});

Request::createFromBase($symfonyRequest);

?>
