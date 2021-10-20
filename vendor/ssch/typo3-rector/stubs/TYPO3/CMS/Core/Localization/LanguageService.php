<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Localization;

if (\class_exists('TYPO3\\CMS\\Core\\Localization\\LanguageService')) {
    return;
}
class LanguageService
{
    /**
     * @return void
     * @param string $language
     */
    public function init($language)
    {
    }
    /**
     * @return void
     */
    public function sL($input)
    {
    }
    /**
     * @return void
     */
    public function getLL($index)
    {
    }
    /**
     * @return void
     */
    public function getLLL($index, $localLanguage)
    {
    }
}
