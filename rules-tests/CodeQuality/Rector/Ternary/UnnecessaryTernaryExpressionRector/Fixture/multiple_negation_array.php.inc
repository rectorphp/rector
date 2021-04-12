<?php

namespace Rector\Tests\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

final class MultipleNegationArray
{
    public function run(array $data)
    {
        return !! $data ? false : true;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

final class MultipleNegationArray
{
    public function run(array $data)
    {
        return ! $data;
    }
}

?>
