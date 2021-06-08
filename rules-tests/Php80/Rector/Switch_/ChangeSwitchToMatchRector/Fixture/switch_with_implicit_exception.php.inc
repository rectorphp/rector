<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class SwitchWithImplicitException
{
    public function run($value)
    {
        switch ($value) {
            case 100:
                return 1000;
        }

        throw new \InvalidArgumentException();
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class SwitchWithImplicitException
{
    public function run($value)
    {
        return match ($value) {
            100 => 1000,
            default => throw new \InvalidArgumentException(),
        };
    }
}

?>
