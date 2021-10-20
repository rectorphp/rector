<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Database\Query;

if (\class_exists('TYPO3\\CMS\\Core\\Database\\Query\\QueryHelper')) {
    return;
}
class QueryHelper
{
    /**
     * @param string $whereClause
     * @return string
     */
    public static function stripLogicalOperatorPrefix($whereClause)
    {
        $whereClause = (string) $whereClause;
        return 'foo';
    }
    /**
     * @param string $groupBy
     * @return mixed[]
     */
    public static function parseGroupBy($groupBy)
    {
        $groupBy = (string) $groupBy;
        return [];
    }
    /**
     * @param string $orderBy
     * @return mixed[]
     */
    public static function parseOrderBy($orderBy)
    {
        $orderBy = (string) $orderBy;
        return [];
    }
}
