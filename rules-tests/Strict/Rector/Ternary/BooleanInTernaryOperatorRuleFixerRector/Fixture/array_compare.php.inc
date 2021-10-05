<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\Ternary\BooleanInTernaryOperatorRuleFixerRector\Fixture;

final class ArrayCompare
{
    public function run(array $data)
    {
        return $data ? 1 : 2;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\Ternary\BooleanInTernaryOperatorRuleFixerRector\Fixture;

final class ArrayCompare
{
    public function run(array $data)
    {
        return $data !== [] ? 1 : 2;
    }
}

?>
