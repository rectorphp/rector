<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;

class SkipSimpleShortArray
{
    /**
     * @var StartAndEnd[]
     */
    private $cachedStartAndEnds = [];

    /**
     * @return StartAndEnd[]
     */
    public function run(StartAndEnd $startAndEnd)
    {
        if ($this->cachedStartAndEnds !== []) {
            return $this->cachedStartAndEnds;
        }

        $this->cachedStartAndEnds[] = $startAndEnd;

        return $this->cachedStartAndEnds;
    }
}
