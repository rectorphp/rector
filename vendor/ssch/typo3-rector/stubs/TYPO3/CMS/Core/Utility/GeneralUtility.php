<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Utility;

if (\class_exists('TYPO3\\CMS\\Core\\Utility\\GeneralUtility')) {
    return;
}
class GeneralUtility
{
    const SYSLOG_SEVERITY_INFO = 0;
    const SYSLOG_SEVERITY_NOTICE = 1;
    const SYSLOG_SEVERITY_WARNING = 2;
    const SYSLOG_SEVERITY_ERROR = 3;
    const SYSLOG_SEVERITY_FATAL = 4;
    /**
     * @return void
     */
    public static function getApplicationContext()
    {
    }
    /**
     * @return object
     * @param string $class
     */
    public static function makeInstance($class, ...$constructorArguments)
    {
        $class = (string) $class;
        return new $class(...$constructorArguments);
    }
    /**
     * @return void
     * @param string $class
     */
    public static function getUserObj($class)
    {
    }
    /**
     * @param string $var
     * @return string
     */
    public static function getIndpEnv($var)
    {
        $var = (string) $var;
        return 'foo';
    }
    /**
     * @param string $folder
     */
    public static function mkdir_deep($folder)
    {
        $folder = (string) $folder;
        return 'foo';
    }
    /**
     * @return string
     */
    public static function explodeUrl2Array($string, $multidim = null)
    {
        return 'foo';
    }
    /**
     * @return string
     */
    public static function logDeprecatedFunction()
    {
        return 'foo';
    }
    /**
     * @return string
     */
    public static function logDeprecatedViewHelperAttribute()
    {
        return 'foo';
    }
    /**
     * @param string $message
     * @return string
     */
    public static function deprecationLog($message)
    {
        $message = (string) $message;
        return isset($message) ? $message : '';
    }
    /**
     * @return string
     */
    public static function getDeprecationLogFileName()
    {
        return 'foo';
    }
    /**
     * @return string
     */
    public static function makeInstanceService($serviceType, $serviceSubType = '', $excludeServiceKeys = [])
    {
        return 'foo';
    }
    /**
     * @return mixed[]
     */
    public static function trimExplode($delim, $string, $removeEmptyValues = \false, $limit = 0)
    {
        return [];
    }
    /**
     * @return string
     */
    public static function idnaEncode($value)
    {
        return 'foo';
    }
    /**
     * @return bool
     */
    public static function isRunningOnCgiServerApi()
    {
        return \false;
    }
    /**
     * @return void
     */
    public static function getUrl($url, $includeHeader = 0, $requestHeaders = null, &$report = null)
    {
    }
    /**
     * @param string $hex
     * @return string
     */
    public static function IPv6Hex2Bin($hex)
    {
        $hex = (string) $hex;
        return '';
    }
    /**
     * @param string $bin
     * @return string
     */
    public static function IPv6Bin2Hex($bin)
    {
        $bin = (string) $bin;
        return '';
    }
    /**
     * @param string $address
     * @return string
     */
    public static function compressIPv6($address)
    {
        $address = (string) $address;
        return '';
    }
    /**
     * @return int
     */
    public static function milliseconds()
    {
        return 1;
    }
    /**
     * @return mixed[]
     */
    public static function intExplode($delimiter, $limit)
    {
        return [];
    }
    /**
     * @return void
     */
    public static function verifyFilenameAgainstDenyPattern($filename)
    {
    }
    /**
     * @return void
     */
    public static function getFileAbsFileName($filename)
    {
    }
    /**
     * @return string
     */
    public static function generateRandomBytes($bytesToReturn)
    {
        return 'bytes';
    }
    /**
     * @return string
     */
    public static function getRandomHexString($count)
    {
        return 'hex';
    }
    /**
     * @return void
     */
    public static function requireOnce($requireFile)
    {
    }
    /**
     * @return void
     */
    public static function requireFile($requireFile)
    {
    }
    /**
     * @return string
     */
    public static function strtoupper($str)
    {
        return 'FOO';
    }
    /**
     * @return string
     */
    public static function strtolower($str)
    {
        return 'foo';
    }
    /**
     * @return void
     */
    public static function loadTCA()
    {
    }
    /**
     * @return int
     */
    public static function int_from_ver($verNumberStr)
    {
        return 1;
    }
    /**
     * @return string
     * @param mixed[] $array
     * @param mixed[] $options
     */
    public static function array2xml_cs($array, $docTag = 'phparray', $options = [], $charset = '')
    {
        // Set default charset unless explicitly specified
        $charset = $charset ?: 'utf-8';
        // Return XML:
        return '<?xml version="1.0" encoding="' . \htmlspecialchars($charset) . '" standalone="yes" ?>' . LF . self::array2xml($array, '', 0, $docTag, 0, $options);
    }
    /**
     * @return string
     * @param mixed[] $array
     * @param mixed[] $options
     * @param mixed[] $stackData
     */
    public static function array2xml($array, $NSprefix = '', $level = 0, $docTag = 'phparray', $spaceInd = 0, $options = [], $stackData = [])
    {
        return 'xml';
    }
    /**
     * @return void
     * @param mixed[] $row
     */
    public static function csvValues($row, $delim = ',', $quote = '"')
    {
    }
    /**
     * @return void
     */
    public static function compat_version()
    {
    }
    /**
     * @return void
     */
    public static function convertMicrotime()
    {
    }
    /**
     * @return void
     */
    public static function deHSCentities()
    {
    }
    /**
     * @return void
     */
    public static function slashJS()
    {
    }
    /**
     * @return void
     */
    public static function rawUrlEncodeJS()
    {
    }
    /**
     * @return void
     */
    public static function rawUrlEncodeFP()
    {
    }
    /**
     * @return void
     */
    public static function lcfirst()
    {
    }
    /**
     * @return void
     */
    public static function getMaximumPathLength()
    {
    }
    /**
     * @return void
     */
    public static function wrapJS($string, $_ = null)
    {
    }
    /**
     * @return void
     */
    public static function readLLfile($fileRef, $langKey, $charset = '', $errorMode = 0)
    {
    }
    public static function isFirstPartOfStr($str, $partStr)
    {
    }
    /**
     * @param string $string
     * @return string
     */
    public static function implodeArrayForUrl($string, $cHash_array)
    {
        $string = (string) $string;
        return 'foo';
    }
    /**
     * @param string $getIndpEnv
     * @return bool
     */
    public static function cmpIP($getIndpEnv, $devIPmask)
    {
        $getIndpEnv = (string) $getIndpEnv;
        return \false;
    }
    public static function uniqueList($in_list, $secondParameter = null)
    {
        return [];
    }
    public static function devLog($msg, $extKey, $severity = 0, $dataVar = \false)
    {
    }
    public static function sysLog($msg, $extKey, $severity = 0)
    {
    }
    public static function initSysLog()
    {
    }
}
