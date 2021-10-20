<?php

namespace RectorPrefix20211020\TYPO3\CMS\Frontend\Page;

use RectorPrefix20211020\TYPO3\CMS\Core\Cache\CacheManager;
use RectorPrefix20211020\TYPO3\CMS\Core\Utility\GeneralUtility;
if (\class_exists('TYPO3\\CMS\\Frontend\\Page\\PageRepository')) {
    return;
}
class PageRepository
{
    /**
     * @var int
     */
    public $versioningWorkspaceId = 0;
    /**
     * @return void
     */
    public function enableFields($table, $show_hidden = -1, $ignore_array = [], $noVersionPreview = \false)
    {
    }
    /**
     * @return string
     */
    public function init($show_hidden)
    {
        return 'foo';
    }
    /**
     * @param int $itera
     * @param bool $disableGroupCheck
     * @return mixed[]
     * @param mixed[] $pageLog
     */
    public function getPageShortcut($SC, $mode, $thisUid, $itera, $pageLog, $disableGroupCheck)
    {
        $itera = (int) $itera;
        $disableGroupCheck = (bool) $disableGroupCheck;
        return [];
    }
    /**
     * @return mixed[]
     */
    public function getFirstWebPage($uid)
    {
        return [];
    }
    /**
     * @return mixed[]
     */
    public function getMenu($pageId, $fields = '*', $sortField = 'sorting', $additionalWhereClause = '', $checkShortcuts = \true)
    {
        return [];
    }
    /**
     * @return mixed[]
     */
    public function getRootLine($uid, $MP = '', $ignoreMPerrors = null)
    {
        return [];
    }
    /**
     * @return void
     */
    public static function storeHash($hash, $data, $ident, $lifetime = 0)
    {
    }
    /**
     * @return void
     */
    public static function getHash($hash)
    {
        \RectorPrefix20211020\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('cache_hash')->get($hash) !== null ? \RectorPrefix20211020\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('cache_hash')->get($hash) : null;
    }
}
