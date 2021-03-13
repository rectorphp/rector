<?php

namespace Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\FixtureExtraFiles;

use Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\Source\AbstractChild;

final class ExtendsAbstractChild extends AbstractChild
{
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\FixtureExtraFiles;

use Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\Source\AbstractChild;

final class ExtendsAbstractChild extends \Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\Source\AbstractMain
{
}

?>
