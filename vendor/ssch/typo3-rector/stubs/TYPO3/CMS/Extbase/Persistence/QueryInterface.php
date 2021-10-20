<?php

declare (strict_types=1);
namespace RectorPrefix20211020\TYPO3\CMS\Extbase\Persistence;

if (\interface_exists('TYPO3\\CMS\\Extbase\\Persistence\\QueryInterface')) {
    return;
}
interface QueryInterface
{
    public function logicalAnd($constraint1);
    public function logicalOr($constraint1);
}
