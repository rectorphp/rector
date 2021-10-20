<?php

namespace RectorPrefix20211020\TYPO3\CMS\Backend\Domain\Repository\Localization;

if (\class_exists('TYPO3\\CMS\\Backend\\Domain\\Repository\\Localization\\LocalizationRepository')) {
    return;
}
class LocalizationRepository
{
    /**
     * @param int $pageId
     * @param int $localizedLanguage
     * @return mixed[]
     */
    public function fetchOriginLanguage($pageId, $localizedLanguage)
    {
        $pageId = (int) $pageId;
        $localizedLanguage = (int) $localizedLanguage;
        return [];
    }
    /**
     * @param int $pageId
     * @param int $languageId
     * @return int
     */
    public function getLocalizedRecordCount($pageId, $languageId)
    {
        $pageId = (int) $pageId;
        $languageId = (int) $languageId;
        return 1;
    }
    /**
     * @param int $pageId
     * @param int $languageId
     * @return mixed[]
     */
    public function fetchAvailableLanguages($pageId, $languageId)
    {
        $pageId = (int) $pageId;
        $languageId = (int) $languageId;
        return [];
    }
    /**
     * @return void
     * @param int $pageId
     * @param int $destLanguageId
     * @param int $languageId
     * @param string $fields
     */
    public function getRecordsToCopyDatabaseResult($pageId, $destLanguageId, $languageId, $fields = '*')
    {
    }
    /**
     * @return void
     */
    public function getUsedLanguagesInPage()
    {
    }
    /**
     * @return void
     */
    public function getUsedLanguagesInPageAndColumn()
    {
    }
}
