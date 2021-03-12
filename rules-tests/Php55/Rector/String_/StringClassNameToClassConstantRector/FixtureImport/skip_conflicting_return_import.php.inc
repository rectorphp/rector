<?php

namespace Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\FixtureImport;

use Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\View;

final class SkipConflictingReturnImport
{
    /**
     * @return \Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\Nested\View
     */
    public function countAction()
    {
    }

    /**
     * @return View
     */
    public function getReturn()
    {
    }
}
