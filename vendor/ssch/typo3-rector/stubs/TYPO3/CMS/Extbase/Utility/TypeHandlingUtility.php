<?php

namespace RectorPrefix20211020\TYPO3\CMS\Extbase\Utility;

if (\class_exists('TYPO3\\CMS\\Extbase\\Utility\\TypeHandlingUtility')) {
    return;
}
class TypeHandlingUtility
{
    /**
     * @return string
     */
    public static function hex2bin($hexadecimalData)
    {
        return 'foo';
    }
    /**
     * @return void
     */
    public static function normalizeType($type)
    {
    }
    /**
     * @return void
     */
    public static function isLiteral($type)
    {
    }
    /**
     * @return void
     */
    public static function isSimpleType($type)
    {
    }
    /**
     * @return void
     */
    public static function parseType($type)
    {
    }
}
