<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Page;

if (\class_exists('TYPO3\\CMS\\Core\\Page\\PageRenderer')) {
    return;
}
class PageRenderer
{
    /**
     * @return void
     */
    public function getConcatenateFiles()
    {
    }
    /**
     * @return void
     */
    public function getConcatenateCss()
    {
    }
    /**
     * @return void
     */
    public function getConcatenateJavascript()
    {
    }
    /**
     * @return void
     */
    public function enableConcatenateFiles()
    {
    }
    /**
     * @return void
     */
    public function disableConcatenateFiles()
    {
    }
    /**
     * @return void
     */
    public function enableConcatenateJavascript()
    {
    }
    /**
     * @return void
     */
    public function enableConcatenateCss()
    {
    }
    /**
     * @return void
     */
    public function disableConcatenateJavascript()
    {
    }
    /**
     * @return void
     */
    public function disableConcatenateCss()
    {
    }
    /**
     * @return void
     */
    public function addMetaTag($meta)
    {
    }
    /**
     * @return void
     * @param string $type
     * @param string $name
     * @param string $content
     * @param mixed[] $subProperties
     */
    public function setMetaTag($type, $name, $content, $subProperties = [], $replace = \true)
    {
    }
    /**
     * @return void
     */
    public function addCssFile($file, $rel = 'stylesheet', $media = 'all', $title = '', $compress = \true, $forceOnTop = \false, $allWrap = '', $excludeFromConcatenation = \false, $splitChar = '|', $inline = \false)
    {
    }
    /**
     * @return void
     */
    public function addJsFile($file, $type = 'text/javascript', $compress = \true, $forceOnTop = \false, $allWrap = '', $excludeFromConcatenation = \false, $splitChar = '|', $async = \false, $integrity = '', $defer = \false, $crossorigin = '')
    {
    }
}
