<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\AddPregQuoteDelimiterRector\Fixture;

class Skip
{
    public function test()
    {
        return preg_quote('name', '_');
    }
}
