<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector\Fixture;

final class SkipMagicArray
{
    public function run($magicNames)
    {
        $names = [$magicNames];

        return compact($names);
    }
}
