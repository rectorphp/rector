<?php

namespace Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;

class ClassWithMultipleTraits extends AnotherParentObject
{
}

?>
-----
<?php

namespace Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;

class ClassWithMultipleTraits
{
    use \Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    use \Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SecondTrait;
}

?>
