<?php

namespace Rector\Renaming\Tests\Rector\FuncCall\RenameFunctionAutoimportRector\Fixture;

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

namespace Rector\Renaming\Tests\Rector\FuncCall\RenameFunctionAutoimportRector\Fixture;

use function Laravel\Templating\render;

class Fixture
{
    public function someMethod()
    {
        render('template', []);
    }
}

function a(callable $a) {
    $a();
}

?>
