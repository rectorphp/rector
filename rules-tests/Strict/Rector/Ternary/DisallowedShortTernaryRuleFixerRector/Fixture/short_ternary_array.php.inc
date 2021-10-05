<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\Ternary\DisallowedShortTernaryRuleFixerRector\Fixture;

final class ShortTernaryArray
{
    public function run(array $array)
    {
        return $array ?: 2;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\Ternary\DisallowedShortTernaryRuleFixerRector\Fixture;

final class ShortTernaryArray
{
    public function run(array $array)
    {
        return $array !== [] ? $array : 2;
    }
}

?>
