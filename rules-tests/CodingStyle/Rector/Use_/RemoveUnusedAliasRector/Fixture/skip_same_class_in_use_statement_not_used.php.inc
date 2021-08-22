<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\StandaloneClass;
use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\DifferentNamespace\StandaloneClass as StandaloneClassDifferentNamespace;

class SkipSameClassInUseStatementNotUsed
{
    public function run()
    {
        return new StandaloneClassDifferentNamespace();
    }
}
