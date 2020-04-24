<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector\Fixture;

class SkipUnknown
{
    public function run()
    {
        $names = ['one', 'two'];

        return compact($names);
    }
}
