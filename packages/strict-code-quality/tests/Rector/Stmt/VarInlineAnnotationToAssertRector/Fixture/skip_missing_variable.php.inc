<?php

namespace Rector\StrictCodeQuality\Tests\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture;

class SkipMissingVariable
{
    public function run($value)
    {
        /** @var int */
        $int = $value;
    }
}
