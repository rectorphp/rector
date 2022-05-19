<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class DefaultInPreviousNotUsedNextSwitch
{
    public function run($input)
    {
        $result = '';
        switch ($input) {
            case 'a':
            case 'b':
                $result = 0;
                break;
            case 'Monsieur':
                $result = 1;
                break;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class DefaultInPreviousNotUsedNextSwitch
{
    public function run($input)
    {
        $result = match ($input) {
            'a', 'b' => 0,
            'Monsieur' => 1,
            default => '',
        };
    }
}

?>
