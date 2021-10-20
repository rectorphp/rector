<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Localization;

if (\class_exists('TYPO3\\CMS\\Core\\Localization\\LocalizationFactory')) {
    return;
}
class LocalizationFactory
{
    /**
     * @return void
     */
    public function getParsedData($fileRef, $langKey, $charset, $errorMode)
    {
    }
}
