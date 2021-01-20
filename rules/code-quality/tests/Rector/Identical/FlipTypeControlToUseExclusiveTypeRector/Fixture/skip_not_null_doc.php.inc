<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use DateTime;
use stdClass;

class SkipNotNullDoc
{
    public function run()
    {
        /** @var stdClass|DateTime $stdClass */
        $stdClass = $this->getStdClass();
        if ($stdClass === null) {
            return;
        }
    }
}

?>
