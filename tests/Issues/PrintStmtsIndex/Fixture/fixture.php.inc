<?php

use Illuminate\Http\Request;

Route::middleware('something')->group(function () {
    Route::get('/some', 'SomeController@some');
    Route::get('/some/{id?}', 'SomeController@some');
});

?>
-----
<?php

use Illuminate\Http\Request;

\Illuminate\Support\Facades\Route::middleware('something')->group(function () {
    \Illuminate\Support\Facades\Route::get('/some', 'SomeController@some');
    \Illuminate\Support\Facades\Route::get('/some/{id?}', 'SomeController@some');
});

?>