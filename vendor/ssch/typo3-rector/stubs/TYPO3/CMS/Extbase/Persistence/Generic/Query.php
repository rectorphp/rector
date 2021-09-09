<?php

declare (strict_types=1);
namespace RectorPrefix20210909\TYPO3\CMS\Extbase\Persistence\Generic;

use RectorPrefix20210909\TYPO3\CMS\Extbase\Persistence\QueryInterface;
if (\class_exists('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query')) {
    return;
}
final class Query implements \RectorPrefix20210909\TYPO3\CMS\Extbase\Persistence\QueryInterface
{
    public function logicalAnd($constraint1)
    {
    }
    public function logicalOr($constraint1)
    {
    }
}
