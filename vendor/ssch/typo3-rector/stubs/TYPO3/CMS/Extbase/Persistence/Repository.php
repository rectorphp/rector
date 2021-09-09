<?php

declare (strict_types=1);
namespace RectorPrefix20210909\TYPO3\CMS\Extbase\Persistence;

use RectorPrefix20210909\TYPO3\CMS\Extbase\Persistence\Generic\Query;
if (\class_exists('TYPO3\\CMS\\Extbase\\Persistence\\Repository')) {
    return;
}
class Repository
{
    public function createQuery() : \RectorPrefix20210909\TYPO3\CMS\Extbase\Persistence\Generic\Query
    {
        return new \RectorPrefix20210909\TYPO3\CMS\Extbase\Persistence\Generic\Query();
    }
}
