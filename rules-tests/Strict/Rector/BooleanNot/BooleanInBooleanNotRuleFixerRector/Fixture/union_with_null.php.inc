<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\Fixture;

final class UnionWithNull
{
    /**
     * @var string|null
     */
    private $value;

    public function run()
    {
        if (! $this->value) {
            return 'empty';
        }

        return 'set';
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\Fixture;

final class UnionWithNull
{
    /**
     * @var string|null
     */
    private $value;

    public function run()
    {
        if ($this->value === null) {
            return 'empty';
        }

        return 'set';
    }
}

?>
