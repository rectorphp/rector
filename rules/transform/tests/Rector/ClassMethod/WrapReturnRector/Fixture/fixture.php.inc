<?php

namespace Rector\Transform\Tests\Rector\ClassMethod\WrapReturnRector\Fixture;

use Rector\Transform\Tests\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass;

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

namespace Rector\Transform\Tests\Rector\ClassMethod\WrapReturnRector\Fixture;

use Rector\Transform\Tests\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass;

final class SomeClass extends SomeReturnClass
{
    public function getItem()
    {
        return [1];
    }
}

?>
