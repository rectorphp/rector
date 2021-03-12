<?php

namespace Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector;

class ClassWithExternalConstant3 extends Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject
{
    public $property;
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector;

class ClassWithExternalConstant3
{
    use \Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    public $property;
}

?>
