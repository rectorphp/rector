<?php

namespace Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector;

class ClassWithExternalConstant3 extends Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject
{
    public $property;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector;

class ClassWithExternalConstant3
{
    use \Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    public $property;
}

?>
