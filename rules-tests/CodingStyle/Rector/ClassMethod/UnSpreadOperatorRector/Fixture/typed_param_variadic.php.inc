<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

final class TypedParamVariadic
{
    public function run(int ...$var)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

final class TypedParamVariadic
{
    public function run(array $var = [])
    {
    }
}

?>
