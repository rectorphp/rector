<?php

namespace Rector\Tests\Rector\Argument\ArgumentAdderRector\Fixture;

use Rector\Tests\Rector\Argument\ArgumentAdderRector\Source\ContainerBuilder;

class CustomContainerBuilder extends ContainerBuilder
{
    public function addCompilerPass($pass, $type)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Argument\ArgumentAdderRector\Fixture;

use Rector\Tests\Rector\Argument\ArgumentAdderRector\Source\ContainerBuilder;

class CustomContainerBuilder extends ContainerBuilder
{
    public function addCompilerPass($pass, $type, \SomeType $priority = 0)
    {
    }
}

?>
