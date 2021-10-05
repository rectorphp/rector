<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector\Fixture;

final class ArrayFromFilter
{
    public function run(array $items)
    {
        if ($items) {
            return true;
        }

        return false;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector\Fixture;

final class ArrayFromFilter
{
    public function run(array $items)
    {
        if ($items !== []) {
            return true;
        }

        return false;
    }
}

?>
