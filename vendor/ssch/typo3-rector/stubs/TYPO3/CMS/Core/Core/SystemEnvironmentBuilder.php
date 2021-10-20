<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Core;

if (\class_exists('TYPO3\\CMS\\Core\\Core\\SystemEnvironmentBuilder')) {
    return;
}
class SystemEnvironmentBuilder
{
    /** @internal */
    const REQUESTTYPE_FE = 1;
    /** @internal */
    const REQUESTTYPE_BE = 2;
    /** @internal */
    const REQUESTTYPE_CLI = 4;
    /** @internal */
    const REQUESTTYPE_AJAX = 8;
    /** @internal */
    const REQUESTTYPE_INSTALL = 16;
    /**
     * @param int $entryPointLevel
     * @param int $requestType
     */
    public static function run($entryPointLevel = 0, $requestType = self::REQUESTTYPE_FE)
    {
    }
}
