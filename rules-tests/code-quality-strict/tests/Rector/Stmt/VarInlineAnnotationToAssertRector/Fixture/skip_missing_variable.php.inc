<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Stmt\VarInlineAnnotationToAssertRector\Fixture;

class SkipMissingVariable
{
    public function run($value)
    {
        /** @var int */
        $int = $value;
    }
}
