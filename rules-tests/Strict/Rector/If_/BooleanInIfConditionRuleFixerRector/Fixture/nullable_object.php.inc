<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector\Fixture;

final class NullableObject
{
    public function run(\stdClass|null $value)
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

final class NullableObject
{
    public function run(\stdClass|null $value)
    {
        if ($value instanceof \stdClass) {
            return 'yes';
        }

        return 'no';
    }
}

?>
