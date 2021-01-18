<?php

namespace Rector\TypeDeclaration\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use stdClass;

class SkipNoVarTag
{
    public function run()
    {
        /** a comment */
        $stdClass = $this->getStdClass();
        if ($stdClass === null) {
            return;
        }
    }
}

?>