<?php

namespace Rector\Tests\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

final class NegationBool
{
    public function run(bool $data)
    {
        return ! $data ? false : true;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

final class NegationBool
{
    public function run(bool $data)
    {
        return $data;
    }
}

?>
