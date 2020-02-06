<?php

namespace Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherTrait;
use Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant2 extends ParentObject
{
    use AnotherTrait;
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherTrait;
use Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant2
{
    use \Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    use AnotherTrait;
}

?>
