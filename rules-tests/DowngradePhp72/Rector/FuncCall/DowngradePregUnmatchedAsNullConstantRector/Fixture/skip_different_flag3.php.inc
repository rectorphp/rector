<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class SkipDifferentFlag3
{
    public function run()
    {
        if (!preg_match_all(
            '~' . self::VARIABLE_REGEX . '~x', $route, $matches,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        )) {
            return [$route];
        }
    }
}

?>

