<?php

namespace Rector\Tests\Privatization\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector\Fixture;

class SomeClass extends PossibleAbstractClass
{
}

class PossibleAbstractClass
{
}

?>
-----
<?php

namespace Rector\Tests\Privatization\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector\Fixture;

class SomeClass extends PossibleAbstractClass
{
}

abstract class PossibleAbstractClass
{
}

?>
