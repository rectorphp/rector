<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\FixtureTreatAsNonEmpty;

class StringNullAndFalse
{
    public function run(string|null|false $name)
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

class StringNullAndFalse
{
    public function run(string|null|false $name)
    {
        if ($name === '' || $name === null || $name === false) {
            return 'no name';
        }

        return 'name';
    }
}

?>
