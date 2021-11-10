<?php

namespace RectorPrefix20211110\TYPO3\CMS\Core\Cache\Frontend;

if (\interface_exists('TYPO3\\CMS\\Core\\Cache\\Frontend\\FrontendInterface')) {
    return;
}
interface FrontendInterface
{
    /**
     * @param mixed[] $tags
     */
    public function set($entryIdentifier, $data, $tags = [], $lifetime = null);
    public function get($entryIdentifier);
}
