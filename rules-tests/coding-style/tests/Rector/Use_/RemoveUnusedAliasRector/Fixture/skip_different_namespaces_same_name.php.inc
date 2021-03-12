<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use \Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\StandaloneClass as FirstStandalone;
use \Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\DifferentNamespace\StandaloneClass as SecondStandalone;

class DifferentNamespacesSameClass
{
    public function run()
    {
        $firstStandalone = new FirstStandalone;
        $secondStandalone = new SecondStandalone;
    }
}
