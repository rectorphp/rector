<?php

namespace Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant extends ParentObject
{
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant
{
    use \Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
}

?>
