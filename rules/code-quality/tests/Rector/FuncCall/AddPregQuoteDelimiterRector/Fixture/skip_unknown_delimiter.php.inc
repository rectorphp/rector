<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\AddPregQuoteDelimiterRector\Fixture;

class SkipUnknownDelimiter
{
    public function test()
    {
        $pattern1 = preg_quote('name');
        $pattern2 = '/' . preg_quote('name');
        $pattern3 = preg_quote('name') . '/';
        $pattern4 = '#' . preg_quote('name') . '/';
    }
}
