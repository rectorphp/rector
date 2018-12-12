<?php

namespace Rector\Tests\Rector\ClassMethod\WrapReturnRector\Fixture;

use Rector\Tests\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass;

final class SomeClass extends SomeReturnClass
{
    public function getItem()
    {
        return 1;
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\ClassMethod\WrapReturnRector\Fixture;

use Rector\Tests\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass;

final class SomeClass extends SomeReturnClass
{
    public function getItem()
    {
        return [1];
    }
}

?>
