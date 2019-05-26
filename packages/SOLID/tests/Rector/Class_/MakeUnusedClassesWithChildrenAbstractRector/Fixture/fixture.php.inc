<?php

namespace Rector\SOLID\Tests\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector\Fixture;

class SomeClass extends PossibleAbstractClass
{
}

class PossibleAbstractClass
{
}

?>
-----
<?php

namespace Rector\SOLID\Tests\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector\Fixture;

class SomeClass extends PossibleAbstractClass
{
}

abstract class PossibleAbstractClass
{
}

?>
