<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class SwitchWithDefaultException
{
    public function run($value)
    {
        switch ($value) {
            case 100:
                $statement = 100;
                break;

            default:
                throw new \InvalidArgumentException();
        }

        return $statement;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class SwitchWithDefaultException
{
    public function run($value)
    {
        $statement = match ($value) {
            100 => 100,
            default => throw new \InvalidArgumentException(),
        };

        return $statement;
    }
}

?>
