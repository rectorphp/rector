<?php

namespace Rector\Tests\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use stdClass;

class SkipNoAssign
{
    public function run()
    {
        if ($stdClass === null) {
            return;
        }
    }
}

?>
