<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class CasesMixUpDefault
{
    public function run($value)
    {
        switch ($value) {
            case 'foo':
                $string = 'foo';
                break;
            case 'other':
            default:
                $string = 'bar';
                break;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class CasesMixUpDefault
{
    public function run($value)
    {
        $string = match ($value) {
            'foo' => 'foo',
            default => 'bar',
        };
    }
}

?>
