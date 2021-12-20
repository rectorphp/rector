<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class CasesMixUpDefaultInFirstCaseNoPrev
{
    public function run($value)
    {
        switch ($value) {
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

class CasesMixUpDefaultInFirstCaseNoPrev
{
    public function run($value)
    {
        $string = match ($value) {
            'other' => 'bar',
            default => 'foo',
        };
    }
}

?>
