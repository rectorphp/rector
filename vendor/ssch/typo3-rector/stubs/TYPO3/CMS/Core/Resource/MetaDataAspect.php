<?php

namespace RectorPrefix20210913\TYPO3\CMS\Core\Resource;

if (\class_exists('TYPO3\\CMS\\Core\\Resource\\MetaDataAspect')) {
    return;
}
class MetaDataAspect
{
    /**
     * @return string
     */
    public function get()
    {
        return 'foo';
    }
}
