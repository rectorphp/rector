<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\Fixture;

final class UnionNullBoolean
{
    public function run(bool|null $maye)
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

final class UnionNullBoolean
{
    public function run(bool|null $maye)
    {
        if ($maye === true) {
            return true;
        }

        return false;
    }
}

?>
