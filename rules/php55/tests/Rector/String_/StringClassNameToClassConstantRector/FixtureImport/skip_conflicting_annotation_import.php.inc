<?php

namespace Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\FixtureImport;

use Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\View;

final class SkipConflictingAnnotationImport
{
    /**
     * @View()
     * @return \Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\Nested\View
     */
    public function countAction()
    {
    }
}
