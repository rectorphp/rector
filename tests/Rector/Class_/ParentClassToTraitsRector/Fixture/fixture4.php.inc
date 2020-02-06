<?php

namespace Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant4 extends ParentObject
{
    public $property;
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant4
{
    use \Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    public $property;
}

?>
