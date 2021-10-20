<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Resource\Processing;

if (\class_exists('TYPO3\\CMS\\Core\\Resource\\Processing\\LocalImageProcessor')) {
    return;
}
class LocalImageProcessor
{
    /**
     * @param string $filename
     * @param string $textline1
     * @param string $textline2
     * @param string $textline3
     * @return string
     */
    public function getTemporaryImageWithText($filename, $textline1, $textline2, $textline3)
    {
        $filename = (string) $filename;
        $textline1 = (string) $textline1;
        $textline2 = (string) $textline2;
        $textline3 = (string) $textline3;
        return 'foo';
    }
}
