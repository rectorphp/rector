<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector\Fixture;

class SkipDoubleCase {

    public function action($var)
    {
        // work done

        switch ($var) {
            case 3:
                $a = 777;
                break;
            case 4:
                $a = 777;
                break;
            default:
                $points = 0;
        }
    }
}
