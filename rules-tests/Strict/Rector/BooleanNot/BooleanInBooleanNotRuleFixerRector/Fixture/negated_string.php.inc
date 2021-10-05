<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\Fixture;

final class NegatedString
{
    private string $name = '';

    public function run()
    {
        if (!$this->name) {
            return 'name';
        }

        return 'no name';
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\Fixture;

final class NegatedString
{
    private string $name = '';

    public function run()
    {
        if ($this->name === '') {
            return 'name';
        }

        return 'no name';
    }
}

?>
