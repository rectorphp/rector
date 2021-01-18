<?php

namespace Rector\TypeDeclaration\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

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
