<?php

namespace Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherTrait;
use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant2 extends ParentObject
{
    use AnotherTrait;
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherTrait;
use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant2
{
    use \Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    use AnotherTrait;
}

?>
