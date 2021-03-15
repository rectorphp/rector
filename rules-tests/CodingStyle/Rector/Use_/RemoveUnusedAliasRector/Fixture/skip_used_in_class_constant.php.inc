<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use App\Bar as BarAlias;

class SkipUsedInClassConstant
{
    private $classMap = [
        BarAlias::class => 'bar',
    ];
}
