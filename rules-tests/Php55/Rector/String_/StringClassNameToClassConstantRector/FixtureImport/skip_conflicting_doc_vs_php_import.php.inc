<?php

namespace Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\FixtureImport;

use Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\View;

class SkipConflictingDocVsPhpImport
{
    /**
     * @var \Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\Nested\View
     */
    public $view;

    public function __construct(View $view)
    {
    }

    /**
     * @return \Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\Nested\View
     */
    public function getView()
    {
        return $this->view;
    }
}
