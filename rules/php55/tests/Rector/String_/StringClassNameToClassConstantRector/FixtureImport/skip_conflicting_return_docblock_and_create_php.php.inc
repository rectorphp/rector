<?php

namespace Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\FixtureImport;

use Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\View;

final class SkipConflictingDocBlockAndCreatePhp
{
    public function countAction()
    {
        return new \Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\Nested\View;
    }

    /**
     * @return View
     */
    public function getReturn()
    {
    }
}
