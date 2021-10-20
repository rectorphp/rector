<?php

namespace RectorPrefix20211020\TYPO3\CMS\Extbase\Domain\Model;

if (\class_exists('TYPO3\\CMS\\Extbase\\Domain\\Model\\BackendUser')) {
    return;
}
class BackendUser
{
    /**
     * @return int
     */
    public function getUid()
    {
        return 1;
    }
}
