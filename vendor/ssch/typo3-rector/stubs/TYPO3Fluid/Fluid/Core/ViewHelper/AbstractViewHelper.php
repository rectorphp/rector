<?php

namespace RectorPrefix20211110\TYPO3Fluid\Fluid\Core\ViewHelper;

use RectorPrefix20211110\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
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
