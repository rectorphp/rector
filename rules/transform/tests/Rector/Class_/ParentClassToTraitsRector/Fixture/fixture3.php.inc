<?php

namespace Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector;

class ClassWithExternalConstant3 extends Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject
{
    public $property;
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector;

class ClassWithExternalConstant3
{
    use \Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    public $property;
}

?>
