<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Imaging;

if (\class_exists('TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions')) {
    return;
}
class GraphicalFunctions
{
    /**
     * @var string
     */
    public $tempPath = 'typo3temp/';
    /**
     * @return void
     */
    public function prependAbsolutePath($fontFile)
    {
    }
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
    /**
     * @return void
     */
    public function init()
    {
    }
    public function createTempSubDir($dirName)
    {
    }
}
