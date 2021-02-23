<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\FixtureExtraFiles;

use Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Source\AbstractChild;

final class ExtendsAbstractChild extends AbstractChild
{
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\FixtureExtraFiles;

use Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Source\AbstractChild;

final class ExtendsAbstractChild extends \Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Source\AbstractMain
{
}

?>
