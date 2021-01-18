<?php

namespace Rector\TypeDeclaration\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use stdClass;

class SkipAssignDifferentVar
{
    public function run($stdClass)
    {
        /** @var stdClass|null $foo */
        $foo = $this->getStdClass();
        if ($stdClass === null) {
            return;
        }
    }
}

?>
