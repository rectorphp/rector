<?php

namespace Rector\Tests\CodeQuality\Rector\Expression\TernaryFalseExpressionToIfRector\Fixture;

final class NegatedValue
{
    public function run($value)
    {
        ! $value ? 'execute' : false;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Expression\TernaryFalseExpressionToIfRector\Fixture;

final class NegatedValue
{
    public function run($value)
    {
        if (! $value) {
            'execute';
        }
    }
}

?>
