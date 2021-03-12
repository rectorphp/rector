<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\CompactToVariablesRector\Fixture;

final class SkipMagicArray
{
    public function run($magicNames)
    {
        $names = [$magicNames];

        return compact($names);
    }
}
