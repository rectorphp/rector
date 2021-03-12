<?php

// @covers https://github.com/rectorphp/rector/issues/786

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

class SkipPregMatchArray
{
    function pregMatchArray()
    {
        $version = 'hi';
        preg_match('#\d\.\d(\.\d)?(-?\S*)?#i', $version, $matches);

        if (count($matches) > 0) {
            return 'found';
        }

        preg_match_all('#\d\.\d(\.\d)?(-?\S*)?#i', $version, $matches);

        if (count($matches) > 0) {
            return 'found';
        }

        return 'none';
    }
}
