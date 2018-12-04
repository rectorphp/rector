<?php

namespace Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Wrong;

use Rector;

class ClassWithExternalConstant3 extends Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject
{
    public $property;
}

?>
-----
<?php

namespace Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Wrong;

use Rector;

class ClassWithExternalConstant3
{
    use \Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    public $property;
}

?>
