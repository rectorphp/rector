<?php

namespace Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherTrait;
use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant2 extends ParentObject
{
    use AnotherTrait;
}

?>
-----
<?php

namespace Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherTrait;
use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant2
{
    use \Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    use AnotherTrait;
}

?>
