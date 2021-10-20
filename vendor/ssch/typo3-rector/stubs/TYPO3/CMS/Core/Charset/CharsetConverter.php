<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Charset;

if (\class_exists('TYPO3\\CMS\\Core\\Charset\\CharsetConverter')) {
    return;
}
class CharsetConverter
{
    /**
     * @return string
     */
    public function getPreferredClientLanguage($languageCodesList)
    {
        return 'foo';
    }
    /**
     * @return void
     */
    public function strlen($charset, $string)
    {
    }
    /**
     * @return void
     */
    public function convCapitalize($charset, $string)
    {
    }
    /**
     * @return void
     */
    public function substr($charset, $string, $start, $len = null)
    {
    }
    /**
     * @return void
     */
    public function conv_case($charset, $string, $case)
    {
    }
    /**
     * @return void
     */
    public function utf8_strpos($haystack, $needle, $offset = 0)
    {
    }
    /**
     * @return void
     */
    public function utf8_strrpos($haystack, $needle)
    {
    }
    /**
     * @return void
     */
    public function conv($inputString, $fromCharset, $toCharset, $useEntityForNoChar = \false)
    {
    }
}
