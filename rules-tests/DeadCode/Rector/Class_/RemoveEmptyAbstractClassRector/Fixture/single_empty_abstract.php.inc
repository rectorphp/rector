<?php

namespace Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

abstract class SingleEmptyAbstract
{
}

class Single extends SingleEmptyAbstract
{
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

class Single
{
}

?>
