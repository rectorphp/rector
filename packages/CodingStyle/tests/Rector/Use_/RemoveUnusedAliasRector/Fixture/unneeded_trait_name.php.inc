<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\ClassicTrait;
use Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\StandaloneTrait as DuplicatedStandaloneTrait;

trait UnneededStandaloneTrait
{
    use ClassicTrait, DuplicatedStandaloneTrait;
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\ClassicTrait;
use Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\StandaloneTrait;

trait UnneededStandaloneTrait
{
    use ClassicTrait, StandaloneTrait;
}

?>
