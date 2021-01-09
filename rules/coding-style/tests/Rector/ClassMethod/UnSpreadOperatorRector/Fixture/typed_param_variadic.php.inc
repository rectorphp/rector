<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

final class TypedParamVariadic
{
    public function run(int ...$var)
    {
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

final class TypedParamVariadic
{
    public function run(array $var)
    {
    }
}

?>
