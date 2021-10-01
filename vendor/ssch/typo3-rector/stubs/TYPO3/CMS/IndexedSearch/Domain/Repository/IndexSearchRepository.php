<?php

namespace RectorPrefix20211001\TYPO3\CMS\IndexedSearch\Domain\Repository;

if (\class_exists('TYPO3\\CMS\\IndexedSearch\\Domain\\Repository\\IndexSearchRepository')) {
    return;
}
class IndexSearchRepository
{
    const WILDCARD_LEFT = 'foo';
    const WILDCARD_RIGHT = 'foo';
}
