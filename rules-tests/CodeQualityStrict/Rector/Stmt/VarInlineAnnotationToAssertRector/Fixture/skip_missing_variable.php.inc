<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture;

class SkipMissingVariable
{
    public function run($value)
    {
        /** @var int */
        $int = $value;
    }
}
