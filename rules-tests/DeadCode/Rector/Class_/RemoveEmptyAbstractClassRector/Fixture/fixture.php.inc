<?php

namespace Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

abstract class AnotherAbstractClass
{
     public function getName()
     {
        return 'name';
     }
}

abstract class SomeAbstractClass extends AnotherAbstractClass
{
}

class SomeClass extends SomeAbstractClass
{
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

abstract class AnotherAbstractClass
{
     public function getName()
     {
        return 'name';
     }
}

class SomeClass extends \Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture\AnotherAbstractClass
{
}

?>
