<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\Fixture;

final class UnionWithStringNull
{
    public function run(string|null $value)
    {
        if (!$value) {
            return 'empty';
        }

        return 'full';
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\Fixture;

final class UnionWithStringNull
{
    public function run(string|null $value)
    {
        if ($value === null || $value === '') {
            return 'empty';
        }

        return 'full';
    }
}

?>
