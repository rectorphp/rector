<?php

namespace RectorPrefix20210620\TYPO3\CMS\Core\Cache\Frontend;

if (\interface_exists('TYPO3\\CMS\\Core\\Cache\\Frontend\\FrontendInterface')) {
    return;
}
interface FrontendInterface
{
    public function set($entryIdentifier, $data, array $tags = [], $lifetime = null);
    public function get($entryIdentifier);
}
