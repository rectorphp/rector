<?php

namespace Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;

class ClassWithMultipleTraits extends AnotherParentObject
{
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;

class ClassWithMultipleTraits
{
    use \Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    use \Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\SecondTrait;
}

?>
