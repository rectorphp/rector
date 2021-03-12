<?php

namespace Rector\Tests\Renaming\Rector\FuncCall\RenameFunctionAutoImportRector\Fixture;

class Fixture
{
    public function someMethod()
    {
        service('template', []);
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\FuncCall\RenameFunctionAutoImportRector\Fixture;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
class Fixture
{
    public function someMethod()
    {
        service('template', []);
    }
}

?>
