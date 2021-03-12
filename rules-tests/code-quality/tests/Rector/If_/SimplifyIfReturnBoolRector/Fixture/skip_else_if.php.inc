<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

class SkipElseIf
{
    public function run()
    {
        if ($someName && $someObject->someName == $someName) {
            return true;
        } else if (!$someName && $anotherObject->someName == $funnel->someName) {
            return true;
        }

        return false;
    }
}
