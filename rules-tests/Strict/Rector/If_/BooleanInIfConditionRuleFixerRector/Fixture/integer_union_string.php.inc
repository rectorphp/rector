<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector\Fixture;

final class IntegerUnionString
{
    public function run(int|string $value)
    {
        if ($value) {
            return 'name';
        }

        return 'no name';
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector\Fixture;

final class IntegerUnionString
{
    public function run(int|string $value)
    {
        if ($value !== 0 || $value !== '') {
            return 'name';
        }

        return 'no name';
    }
}

?>
