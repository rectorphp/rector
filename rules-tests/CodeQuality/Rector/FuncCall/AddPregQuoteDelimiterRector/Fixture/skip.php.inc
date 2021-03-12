<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector\Fixture;

class Skip
{
    public function test()
    {
        return preg_quote('name', '_');
    }
}
