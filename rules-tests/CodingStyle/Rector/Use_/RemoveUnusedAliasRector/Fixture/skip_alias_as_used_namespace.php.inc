<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\DifferentNamespace as Foo;

class SkipAliasAsUsedNamespace
{
    public function run()
    {
        $foo = new Foo\StandaloneClass();
    }
}

?>
