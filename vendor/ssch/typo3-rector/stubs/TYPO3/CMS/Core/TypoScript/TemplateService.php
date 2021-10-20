<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\TypoScript;

if (\class_exists('TYPO3\\CMS\\Core\\TypoScript\\TemplateService')) {
    return;
}
class TemplateService
{
    /**
     * @var bool
     */
    public $forceTemplateParsing = \false;
    /**
     * @return void
     */
    public function getFileName($file)
    {
    }
    /**
     * @return void
     */
    public function fileContent($fileName)
    {
    }
    /**
     * @return void
     */
    public static function init()
    {
    }
}
