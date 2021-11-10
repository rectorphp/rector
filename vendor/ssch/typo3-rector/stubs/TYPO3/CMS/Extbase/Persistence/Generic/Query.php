<?php

declare (strict_types=1);
namespace RectorPrefix20211110\TYPO3\CMS\Extbase\Persistence\Generic;

use RectorPrefix20211110\TYPO3\CMS\Extbase\Persistence\QueryInterface;
if (\class_exists('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query')) {
    return;
}
final class Query implements \RectorPrefix20211110\TYPO3\CMS\Extbase\Persistence\QueryInterface
{
    public function logicalAnd($constraint1)
    {
    }
    public function logicalOr($constraint1)
    {
    }
}
