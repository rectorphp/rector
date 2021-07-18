<?php

namespace RectorPrefix20210718\TYPO3Fluid\Fluid\Core\ViewHelper;

use RectorPrefix20210718\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
if (\class_exists('TYPO3Fluid\\Fluid\\Core\\ViewHelper\\AbstractViewHelper')) {
    return;
}
class AbstractViewHelper
{
    /**
     * @var RenderingContextInterface
     */
    protected $renderingContext;
    /**
     * @return void
     */
    public function initializeArguments()
    {
    }
}
