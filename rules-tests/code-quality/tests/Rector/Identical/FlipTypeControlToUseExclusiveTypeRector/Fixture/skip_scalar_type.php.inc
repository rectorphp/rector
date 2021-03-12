<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use stdClass;

class SkipScalarType
{
    public function run()
    {
        /** @var string|null $string */
        $string = $this->getString();
        if ($string === null) {
            return;
        }
    }
}

?>
