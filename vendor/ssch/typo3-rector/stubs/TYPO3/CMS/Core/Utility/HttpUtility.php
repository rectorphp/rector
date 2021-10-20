<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Utility;

if (\class_exists('TYPO3\\CMS\\Core\\Utility\\HttpUtility')) {
    return;
}
class HttpUtility
{
    const HTTP_STATUS_400 = 'HTTP/1.1 400 Bad Request';
    /**
     * @return string
     * @param mixed[] $queryParams
     */
    public static function buildQueryString($queryParams)
    {
        return '';
    }
    /**
     * @return void
     * @param string $httpStatus
     */
    public static function setResponseCode($httpStatus)
    {
    }
}
