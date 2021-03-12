<?php

namespace Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant4 extends ParentObject
{
    public $property;
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant4
{
    use \Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    public $property;
}

?>
