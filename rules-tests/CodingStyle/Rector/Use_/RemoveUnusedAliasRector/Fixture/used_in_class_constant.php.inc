<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use App\Bar as BarAlias;
use App\NoAlias;

class UsedInClassConstant
{
    private $classMap = [
        BarAlias::class => 'bar',
        NoAlias::class => 'noalias',
    ];

    public function run()
    {
        BarAlias::execute();
        BarAlias::DATA;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use App\Bar;
use App\NoAlias;

class UsedInClassConstant
{
    private $classMap = [
        Bar::class => 'bar',
        NoAlias::class => 'noalias',
    ];

    public function run()
    {
        Bar::execute();
        Bar::DATA;
    }
}

?>
