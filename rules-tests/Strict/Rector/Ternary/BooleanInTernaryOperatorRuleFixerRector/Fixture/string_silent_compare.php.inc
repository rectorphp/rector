<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\Ternary\BooleanInTernaryOperatorRuleFixerRector\Fixture;

final class StringSilentCompare
{
    public function run(string $string)
    {
        return $string ? 1 : 2;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\Ternary\BooleanInTernaryOperatorRuleFixerRector\Fixture;

final class StringSilentCompare
{
    public function run(string $string)
    {
        return $string !== '' ? 1 : 2;
    }
}

?>
