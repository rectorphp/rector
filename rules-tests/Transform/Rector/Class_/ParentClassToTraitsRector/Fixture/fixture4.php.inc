<?php

namespace Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant4 extends ParentObject
{
    public $property;
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant4
{
    use \Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    public $property;
}

?>
