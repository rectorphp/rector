<?php

namespace RectorPrefix20211020\TYPO3\CMS\Extbase\Mvc\Web\Routing;

if (\class_exists('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder')) {
    return;
}
class UriBuilder
{
    /**
     * @return $this
     * @param string $addQueryStringMethod
     */
    public function setAddQueryStringMethod($addQueryStringMethod)
    {
        $addQueryStringMethod = (string) $addQueryStringMethod;
        return $this;
    }
    /**
     * @return $this
     */
    public function reset()
    {
        return $this;
    }
    /**
     * @return $this
     * @param bool $true
     */
    public function setUseCacheHash($true)
    {
        $true = (bool) $true;
        return $this;
    }
    /**
     * @return $this
     * @param bool $true
     */
    public function setCreateAbsoluteUri($true)
    {
        $true = (bool) $true;
        return $this;
    }
    /**
     * @return $this
     * @param bool $true
     */
    public function setAddQueryString($true)
    {
        $true = (bool) $true;
        return $this;
    }
    /**
     * @return void
     */
    public function build()
    {
    }
}
