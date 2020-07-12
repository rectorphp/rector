<?php

namespace Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\FixtureImport;

use Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\View;

final class SkipConflictingVarDocBlockAndCreatePhp
{
    /**
     * @var View
     */
    public $variable;

    public function countAction()
    {
        return new \Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\Nested\View;
    }
}
