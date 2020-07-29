<?php

namespace Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;

class ClassWithMultipleTraits extends AnotherParentObject
{
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;

class ClassWithMultipleTraits
{
    use \Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    use \Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SecondTrait;
}

?>
