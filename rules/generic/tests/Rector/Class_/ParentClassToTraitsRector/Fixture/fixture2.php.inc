<?php

namespace Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherTrait;
use Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant2 extends ParentObject
{
    use AnotherTrait;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherTrait;
use Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant2
{
    use \Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    use AnotherTrait;
}

?>
