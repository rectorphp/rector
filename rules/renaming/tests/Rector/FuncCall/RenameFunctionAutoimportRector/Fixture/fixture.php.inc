<?php

namespace Rector\Renaming\Tests\Rector\FuncCall\RenameFunctionAutoimportRector\Fixture;

class Fixture
{
    public function someMethod()
    {
        service('template', []);
    }
}

function a(callable $a) {
    $a();
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\FuncCall\RenameFunctionAutoimportRector\Fixture;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class Fixture
{
    public function someMethod()
    {
        service('template', []);
    }
}

function a(callable $a) {
    $a();
}

?>
