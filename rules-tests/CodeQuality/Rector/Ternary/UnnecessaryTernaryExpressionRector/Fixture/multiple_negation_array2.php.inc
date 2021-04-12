<?php

namespace Rector\Tests\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

final class MultipleNegationArray2
{
    public function run(array $data)
    {
        return !! empty($data) ? false : true;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

final class MultipleNegationArray2
{
    public function run(array $data)
    {
        return ! empty($data);
    }
}

?>
