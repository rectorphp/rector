<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector\Fixture;

final class ElseIfNegatedString
{
    private string $name = '';

    public function run(bool $boolValue)
    {
        if ($boolValue) {
            return 'bool';
        } elseif ($this->name) {
            return 'name';
        }

        return 'no name';
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector\Fixture;

final class ElseIfNegatedString
{
    private string $name = '';

    public function run(bool $boolValue)
    {
        if ($boolValue) {
            return 'bool';
        } elseif ($this->name !== '') {
            return 'name';
        }

        return 'no name';
    }
}

?>
