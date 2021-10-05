<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\Fixture;

final class StringUnionInteger
{
    public function run(string|int $maye)
    {
        if (! $maye) {
            return true;
        }

        return false;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\Fixture;

final class StringUnionInteger
{
    public function run(string|int $maye)
    {
        if ($maye === 0 || $maye === '') {
            return true;
        }

        return false;
    }
}

?>
