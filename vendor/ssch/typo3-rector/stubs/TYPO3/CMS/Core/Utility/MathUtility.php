<?php

namespace RectorPrefix20210603\TYPO3\CMS\Core\Utility;

if (\class_exists('TYPO3\\CMS\\Core\\Utility\\MathUtility')) {
    return;
}
class MathUtility
{
    /**
     * @return bool
     */
    public static function canBeInterpretedAsInteger($uid)
    {
        return \true;
    }
}
