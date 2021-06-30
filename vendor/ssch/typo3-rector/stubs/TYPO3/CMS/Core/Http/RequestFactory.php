<?php

namespace RectorPrefix20210630\TYPO3\CMS\Core\Http;

if (\class_exists('TYPO3\\CMS\\Core\\Http\\RequestFactory')) {
    return;
}
class RequestFactory
{
    /**
     * @return void
     * @param string $uri
     * @param string $method
     */
    public function request($uri, $method = 'GET', array $options = [])
    {
    }
}
