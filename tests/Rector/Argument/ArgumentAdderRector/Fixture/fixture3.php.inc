<?php

namespace Rector\Core\Tests\Rector\Argument\ArgumentAdderRector\Fixture;

use Rector\Core\Tests\Rector\Argument\ArgumentAdderRector\Source\SomeContainerBuilder;

class CustomContainerBuilder extends SomeContainerBuilder
{
    public function addCompilerPass($pass, $type)
    {
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Argument\ArgumentAdderRector\Fixture;

use Rector\Core\Tests\Rector\Argument\ArgumentAdderRector\Source\SomeContainerBuilder;

class CustomContainerBuilder extends SomeContainerBuilder
{
    public function addCompilerPass($pass, $type, int $priority = 0)
    {
    }
}

?>
