<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class MultipleCases2
{
    public function run($value)
    {
        switch ($value) {
            case 'v1':
            case 'v2':
            case 'v3':
                return 100;
            case 'v4':
            case 'v5':
            case 'v6':
                return 200;
            default:
                return 1000;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class MultipleCases2
{
    public function run($value)
    {
        return match ($value) {
            'v1', 'v2', 'v3' => 100,
            'v4', 'v5', 'v6' => 200,
            default => 1000,
        };
    }
}

?>
