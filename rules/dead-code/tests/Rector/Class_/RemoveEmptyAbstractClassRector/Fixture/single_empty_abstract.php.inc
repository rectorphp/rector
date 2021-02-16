<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

abstract class SingleEmptyAbstract
{
}

class Single extends SingleEmptyAbstract
{
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

class Single
{
}

?>
