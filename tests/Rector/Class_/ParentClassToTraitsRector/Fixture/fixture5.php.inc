<?php

namespace Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;

class ClassWithMultipleTraits extends AnotherParentObject
{
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;

class ClassWithMultipleTraits
{
    use \Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
    use \Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SecondTrait;
}

?>
