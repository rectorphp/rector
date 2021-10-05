<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\Ternary\DisallowedShortTernaryRuleFixerRector\Fixture;

trait ResetTrait
{
    public function run(array $albums)
    {
        return \reset($albums) ?: null;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\Ternary\DisallowedShortTernaryRuleFixerRector\Fixture;

trait ResetTrait
{
    public function run(array $albums)
    {
        return $albums !== [] ? \reset($albums) : null;
    }
}

?>
