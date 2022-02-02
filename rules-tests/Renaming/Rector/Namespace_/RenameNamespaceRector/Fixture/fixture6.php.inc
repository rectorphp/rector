<?php

namespace App\Repositories;

class BlogRepository {
}

function index()
{
    \App\Repositories\BlogRepository::class;
}

?>
-----
<?php

namespace App\Repositories\Example;

class BlogRepository {
}

function index()
{
    \App\Repositories\Example\BlogRepository::class;
}

?>
