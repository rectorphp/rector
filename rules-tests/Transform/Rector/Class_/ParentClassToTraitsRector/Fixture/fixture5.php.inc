<?php

namespace Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;

class ClassWithMultipleTraits extends AnotherParentObject
{
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;

class ClassWithMultipleTraits
{
    use \Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    use \Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SecondTrait;
}

?>
