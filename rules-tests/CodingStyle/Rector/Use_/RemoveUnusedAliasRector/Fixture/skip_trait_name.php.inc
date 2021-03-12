<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\StandaloneTrait as DuplicatedStandaloneTrait;

trait StandaloneTrait
{
    use DuplicatedStandaloneTrait;
}
