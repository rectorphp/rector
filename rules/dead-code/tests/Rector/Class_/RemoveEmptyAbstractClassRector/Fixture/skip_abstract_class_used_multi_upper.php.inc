<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

abstract class AbstractClassMultiUpper
{
}

abstract class SkipAbstractClassUsedMultiUpper extends AbstractClassMultiUpper
{
}

final class AnyClass3
{
	public function process($some)
    {
        if ($some instanceof AbstractClassMultiUpper) {

        }
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

abstract class AbstractClassMultiUpper
{
}

final class AnyClass3
{
	public function process($some)
    {
        if ($some instanceof AbstractClassMultiUpper) {

        }
    }
}

?>