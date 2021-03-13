<?php

namespace Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant extends ParentObject
{
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant
{
    use \Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
}

?>
