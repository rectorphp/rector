<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class CasesMixUpDefaultInMiddleCase
{
    public function run($value)
    {
        switch ($value) {
            case 'a':
                $string = 'A';
                break;
            case 'foo':
            default:
                $string = 'foo';
                break;
            case 'other':
                $string = 'bar';
                break;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class CasesMixUpDefaultInMiddleCase
{
    public function run($value)
    {
        $string = match ($value) {
            'a' => 'A',
            'other' => 'bar',
            default => 'foo',
        };
    }
}

?>
