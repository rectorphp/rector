<?php

namespace Rector\Tests\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

final class NegationArray2
{
    public function run(array $data)
    {
        return ! empty($data) ? false : true;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

final class NegationArray2
{
    public function run(array $data)
    {
        return empty($data);
    }
}

?>
