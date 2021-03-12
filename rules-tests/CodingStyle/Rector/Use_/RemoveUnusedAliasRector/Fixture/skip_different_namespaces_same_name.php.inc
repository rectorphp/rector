<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use \Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\StandaloneClass as FirstStandalone;
use \Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\DifferentNamespace\StandaloneClass as SecondStandalone;

class DifferentNamespacesSameClass
{
    public function run()
    {
        $firstStandalone = new FirstStandalone;
        $secondStandalone = new SecondStandalone;
    }
}
