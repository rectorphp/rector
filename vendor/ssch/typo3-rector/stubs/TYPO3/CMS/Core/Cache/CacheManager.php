<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Cache;

use RectorPrefix20211020\TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
if (\class_exists('TYPO3\\CMS\\Core\\Cache\\CacheManager')) {
    return;
}
class CacheManager
{
    /**
     * @param string $identifier
     * @return \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    public function getCache($identifier)
    {
        $identifier = (string) $identifier;
        return new \RectorPrefix20211020\TYPO3\CMS\Core\Cache\Anonymous__80f9d48e45a850436cae4f188819f43c__0();
    }
    /**
     * @return void
     */
    public function flushCachesInGroup($group)
    {
    }
}
class Anonymous__80f9d48e45a850436cae4f188819f43c__0 implements \RectorPrefix20211020\TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
{
    /**
     * @param mixed[] $tags
     */
    public function set($entryIdentifier, $data, $tags = [], $lifetime = null) : void
    {
    }
    public function get($entryIdentifier)
    {
        return $entryIdentifier;
    }
}
