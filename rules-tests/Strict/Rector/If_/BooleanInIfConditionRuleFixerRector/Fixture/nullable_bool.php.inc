<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector\Fixture;

final class NullableBool
{
    public function run(bool|null $value)
    {
        if ($value) {
            return 'yes';
        }

        return 'no';
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector\Fixture;

final class NullableBool
{
    public function run(bool|null $value)
    {
        if ($value === true) {
            return 'yes';
        }

        return 'no';
    }
}

?>
