<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\FixtureTreatAsNonEmpty;

class UnionWithNullFromParam
{
    public function run(string|null $name)
    {
        if (! $name) {
            return 'no name';
        }

        return 'name';
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\FixtureTreatAsNonEmpty;

class UnionWithNullFromParam
{
    public function run(string|null $name)
    {
        if ($name === null) {
            return 'no name';
        }

        return 'name';
    }
}

?>
