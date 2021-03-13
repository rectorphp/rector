<?php

namespace Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\FixtureImport;

use Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\View;

final class SkipConflictingAnnotationImport
{
    /**
     * @View()
     * @return \Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\Nested\View
     */
    public function countAction()
    {
    }
}
