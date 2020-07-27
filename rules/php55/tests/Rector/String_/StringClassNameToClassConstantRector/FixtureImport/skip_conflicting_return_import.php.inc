<?php

namespace Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\FixtureImport;

use Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\View;

final class SkipConflictingReturnImport
{
    /**
     * @return \Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\Nested\View
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
