<?php

namespace Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector;

class ClassWithExternalConstant3 extends Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject
{
    public $property;
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector;

class ClassWithExternalConstant3
{
    use \Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    public $property;
}

?>
