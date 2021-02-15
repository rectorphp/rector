<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

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

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

abstract class AnotherAbstractClass
{
     public function getName()
     {
        return 'name';
     }
}

class SomeClass extends AnotherAbstractClass
{
}

?>
