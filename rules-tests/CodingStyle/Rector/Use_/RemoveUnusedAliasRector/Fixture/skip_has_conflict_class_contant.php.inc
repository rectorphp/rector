<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use App\Bar as BarAlias;
use App\Foo\Bar;
use App\NoAlias;

class SkipHasConflictClassConstant
{
    private $classMap = [
        BarAlias::class => 'bar',
        NoAlias::class => 'noalias',
    ];

    public function run()
    {
        Bar::execute();
        Bar::DATA;
    }
}

?>
