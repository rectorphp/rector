<?php

namespace Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\FixtureImport;

use Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\View;

final class SkipConflictingVarDocBlockAndCreatePhp
{
    /**
     * @var View
     */
    public $variable;

    public function countAction()
    {
        return new \Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\Nested\View;
    }
}
