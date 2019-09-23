<?php

namespace Rector\Tests\Rector\Function_\RenameFunctionRector\Fixture;

class SomeClass
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

namespace Rector\Tests\Rector\Function_\RenameFunctionRector\Fixture;

class SomeClass
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
