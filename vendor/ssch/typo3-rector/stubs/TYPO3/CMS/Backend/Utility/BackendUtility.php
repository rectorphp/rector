<?php

namespace RectorPrefix20211020\TYPO3\CMS\Backend\Utility;

if (\class_exists('TYPO3\\CMS\\Backend\\Utility\\BackendUtility')) {
    return;
}
class BackendUtility
{
    /**
     * @return string
     */
    public static function editOnClick($params, $_ = '', $requestUri = '')
    {
        return 'foo';
    }
    /**
     * @return mixed[]
     */
    public static function getRecordRaw($table, $where = '', $fields = '*')
    {
        return [];
    }
    /**
     * @return string
     */
    public static function TYPO3_copyRightNotice()
    {
        return 'notice';
    }
    /**
     * @return string
     */
    public static function getModuleUrl($moduleName, $urlParameters = [])
    {
        return 'foo';
    }
    /**
     * @return void
     */
    public static function storeHash($hash, $data, $ident)
    {
    }
    /**
     * @return void
     */
    public static function getHash($hash)
    {
    }
    /**
     * @return string
     */
    public static function getViewDomain($pageId, $rootLine = null)
    {
        return 'foo';
    }
    /**
     * @return void
     */
    public static function wrapClickMenuOnIcon($content, $table, $uid = 0, $context = '', $_addParams = '', $_enDisItems = '', $returnTagParameters = \false)
    {
    }
    /**
     * @return string
     */
    public static function thumbCode($row, $table, $field)
    {
        return '';
    }
    /**
     * @param string $url
     * @return bool
     */
    public static function shortcutExists($url)
    {
        $url = (string) $url;
        return \true;
    }
    /**
     * @return int
     */
    public static function getPidForModTSconfig($table, $uid, $pid)
    {
        return 1;
    }
    /**
     * @return void
     */
    public static function getPagesTSconfig($id, $rootLine = null, $returnPartArray = \false)
    {
    }
    /**
     * @return void
     * @param mixed[]|null $rootLine
     */
    public static function getRawPagesTSconfig($id, $rootLine = null)
    {
    }
    /**
     * @return mixed[]
     */
    public static function getRecordsByField($theTable, $theField, $theValue, $whereClause = '', $groupBy = '', $orderBy = '', $limit = '', $useDeleteClause = \true, $queryBuilder = null)
    {
        return [];
    }
    /**
     * @param string $table
     * @param string $context
     */
    public static function getClickMenuOnIconTagParameters($table, $uid = 0, $context = '')
    {
    }
}
