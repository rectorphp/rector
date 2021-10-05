<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\Ternary\BooleanInTernaryOperatorRuleFixerRector\Fixture;

final class StrlenNumber
{
    public function run(string $string)
    {
        return strlen($string) ? 1 : 2;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\Ternary\BooleanInTernaryOperatorRuleFixerRector\Fixture;

final class StrlenNumber
{
    public function run(string $string)
    {
        return strlen($string) !== 0 ? 1 : 2;
    }
}

?>
