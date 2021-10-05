<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\Ternary\DisallowedShortTernaryRuleFixerRector\Fixture;

final class ShortTernaryString
{
    public function run(string $string)
    {
        return $string ?: 2;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\Ternary\DisallowedShortTernaryRuleFixerRector\Fixture;

final class ShortTernaryString
{
    public function run(string $string)
    {
        return $string !== '' ? $string : 2;
    }
}

?>
