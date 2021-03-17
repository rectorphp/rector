<?php

namespace Rector\Tests\Renaming\Rector\FuncCall\RenameFunctionRector\Fixture;

class Fixture
{
    public function someMethod()
    {
        view('template', []);
    }
}

function a(callable $a) {
    $a();
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\FuncCall\RenameFunctionRector\Fixture;

class Fixture
{
    public function someMethod()
    {
        \Laravel\Templating\render('template', []);
    }
}

function a(callable $a) {
    $a();
}

?>
