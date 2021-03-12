<?php

namespace Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\FixtureImport;

use Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\View;

class SkipConflictingDocVsPhpImport
{
    /**
     * @var \Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\Nested\View
     */
    public $view;

    public function __construct(View $view)
    {
    }

    /**
     * @return \Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\Nested\View
     */
    public function getView()
    {
        return $this->view;
    }
}
