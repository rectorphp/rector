<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\Fixture;

final class NegatedInteger
{
    private int $age;

    public function run()
    {
        if (! $this->age) {
            return 'age';
        }

        return 'no age';
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\Fixture;

final class NegatedInteger
{
    private int $age;

    public function run()
    {
        if ($this->age === 0) {
            return 'age';
        }

        return 'no age';
    }
}

?>
