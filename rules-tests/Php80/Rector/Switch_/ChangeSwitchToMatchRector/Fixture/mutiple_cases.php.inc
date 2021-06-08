<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class MultipleCases
{
    public function run($value)
    {
        switch ($value) {
            case 'v1':
            case 'v2':
                return 100;
            default:
                return 1000;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class MultipleCases
{
    public function run($value)
    {
        return match ($value) {
            'v1', 'v2' => 100,
            default => 1000,
        };
    }
}

?>
