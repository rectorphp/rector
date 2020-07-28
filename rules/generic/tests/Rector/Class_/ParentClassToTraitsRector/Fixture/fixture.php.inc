<?php

namespace Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant extends ParentObject
{
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant
{
    use \Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
}

?>
