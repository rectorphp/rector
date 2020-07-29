<?php

namespace Rector\Generic\Tests\Rector\Argument\ArgumentAdderRector\Fixture;

use Rector\Generic\Tests\Rector\Argument\ArgumentAdderRector\Source\SomeContainerBuilder;

class CustomContainerBuilder extends SomeContainerBuilder
{
    public function addCompilerPass($pass, $type)
    {
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Argument\ArgumentAdderRector\Fixture;

use Rector\Generic\Tests\Rector\Argument\ArgumentAdderRector\Source\SomeContainerBuilder;

class CustomContainerBuilder extends SomeContainerBuilder
{
    public function addCompilerPass($pass, $type, int $priority = 0)
    {
    }
}

?>
