<?php

namespace RectorPrefix20211020\TYPO3\CMS\Extbase\Persistence\Generic;

if (\class_exists('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings')) {
    return;
}
class Typo3QuerySettings
{
    /**
     * @var int
     */
    private $languageUid = 0;
    /**
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings
     */
    public function setLanguageMode()
    {
        return $this;
    }
    public function getLanguageMode()
    {
        return null;
    }
    /**
     * @param int $languageUid
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings
     */
    public function setLanguageUid($languageUid)
    {
        $languageUid = (int) $languageUid;
        $this->languageUid = $languageUid;
        return $this;
    }
    /**
     * @return int
     */
    public function getLanguageUid()
    {
        return $this->languageUid;
    }
}
