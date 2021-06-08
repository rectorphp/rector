<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class ChangeDefaultAfter
{
    public function run($input): string
    {
        switch ($input) {
            case 100:
                return 'yes';
            case 200:
                return 'no';
        }

        return 'maybe';
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class ChangeDefaultAfter
{
    public function run($input): string
    {
        return match ($input) {
            100 => 'yes',
            200 => 'no',
            default => 'maybe',
        };
    }
}

?>
