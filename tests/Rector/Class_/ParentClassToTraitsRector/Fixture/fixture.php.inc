<?php

namespace Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant extends ParentObject
{
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Fixture;

use Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;

class ClassWithExternalConstant
{
    use \Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
}

?>
