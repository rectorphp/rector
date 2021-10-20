<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Resource;

if (\class_exists('TYPO3\\CMS\\Core\\Resource\\File')) {
    return;
}
class File
{
    /**
     * @return string
     */
    public function _getMetaData()
    {
        return 'foo';
    }
    /**
     * @return \TYPO3\CMS\Core\Resource\MetaDataAspect
     */
    public function getMetaData()
    {
        return new \RectorPrefix20211020\TYPO3\CMS\Core\Resource\MetaDataAspect();
    }
}
