<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\ClassicTrait;
use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\StandaloneTrait as DuplicatedStandaloneTrait;

trait UnneededStandaloneTrait
{
    use ClassicTrait, DuplicatedStandaloneTrait;
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\ClassicTrait;
use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\StandaloneTrait;

trait UnneededStandaloneTrait
{
    use ClassicTrait, StandaloneTrait;
}

?>
