<?php

namespace Rector\TypeDeclaration\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use stdClass;

class SkipNoDoc
{
    public function run()
    {
        $stdClass = $this->getStdClass();
        if ($stdClass === null) {
            return;
        }
    }
}

?>
