<?php

namespace RectorPrefix20211020\TYPO3\CMS\Frontend\Plugin;

use RectorPrefix20211020\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
if (\class_exists('TYPO3\\CMS\\Frontend\\Plugin\\AbstractPlugin')) {
    return;
}
class AbstractPlugin
{
    /**
     * The backReference to the mother cObj object set at call time
     *
     * @var ContentObjectRenderer
     */
    public $cObj;
    /**
     * @return void
     */
    public function pi_getLL($key, $alternativeLabel = '', $hsc = \false)
    {
    }
}
