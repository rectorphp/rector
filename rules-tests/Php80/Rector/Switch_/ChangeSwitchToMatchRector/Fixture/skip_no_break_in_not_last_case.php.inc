<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class SkipNoBreakInNotLastCase
{
    public function run()
    {
        $value = 1;
        $suffix = 'M';
        switch ($suffix) {
            case 'M':
                $value *= 1024;
            case 'K':
                $value *= 1024;
                break;
        }
    }
}

?>
