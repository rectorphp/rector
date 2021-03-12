<?php

namespace Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\FixtureImport;

use Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\View;

final class SkipConflictingParamDocBlockAndCreatePhp
{
    /**
     * @param View $variable
     */
    public function process($variable)
    {
    }

    public function countAction()
    {
        return new \Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\Nested\View;
    }
}
