<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

abstract class AbstractClassMulti
{
}

abstract class AbstractClassUsedMulti extends AbstractClassMulti
{
}

final class AnyClass2
{
	public function process($some)
    {
        if ($some instanceof AbstractClassUsedMulti) {

        }
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Fixture;

abstract class AbstractClassUsedMulti
{
}

final class AnyClass2
{
	public function process($some)
    {
        if ($some instanceof AbstractClassUsedMulti) {

        }
    }
}

?>