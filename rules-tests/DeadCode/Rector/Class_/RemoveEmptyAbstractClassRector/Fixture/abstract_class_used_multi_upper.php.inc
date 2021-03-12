<?php

namespace Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

abstract class AbstractClassMultiUpper
{
}

abstract class AbstractClassUsedMultiUpper extends AbstractClassMultiUpper
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

namespace Rector\Tests\DeadCode\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

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
