<?php

use stdClass;

Route::middleware('something')->group(function () {
    Route::get('/some', 'SomeController@some');
    Route::get('/some/{id?}', 'SomeController@some');
});

\Request::createFromBase($symfonyRequest);

?>
-----
<?php

use stdClass;

\Illuminate\Support\Facades\Route::middleware('something')->group(function () {
    \Illuminate\Support\Facades\Route::get('/some', 'SomeController@some');
    \Illuminate\Support\Facades\Route::get('/some/{id?}', 'SomeController@some');
});

\Illuminate\Http\Request::createFromBase($symfonyRequest);

?>
