<?php

namespace Rector\TypeDeclaration\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

class SkipSameType
{
    public function run()
    {
        /** @var string|string $string */
        $string = $this->getString();
        if ($string === null) {
            return;
        }
    }
}

?>
