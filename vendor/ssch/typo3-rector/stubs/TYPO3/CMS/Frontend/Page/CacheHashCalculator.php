<?php

namespace RectorPrefix20211020\TYPO3\CMS\Frontend\Page;

if (\class_exists('TYPO3\\CMS\\Frontend\\Page\\CacheHashCalculator')) {
    return;
}
class CacheHashCalculator
{
    /**
     * @return mixed[]
     */
    public function getRelevantParameters($queryParams)
    {
        return [];
    }
}
