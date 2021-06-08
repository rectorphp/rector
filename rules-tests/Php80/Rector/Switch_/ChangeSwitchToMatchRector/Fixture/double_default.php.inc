<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class DoubleDefault
{
    public function run($input)
    {
        $value = null;

        switch ($input) {
            case 100:
                $value = 1000;
                break;
            default:
                throw new \InvalidArgumentException('Not defined');
        }

        return $value;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class DoubleDefault
{
    public function run($input)
    {
        $value = match ($input) {
            100 => 1000,
            default => throw new \InvalidArgumentException('Not defined'),
        };
        return $value;
    }
}

?>
