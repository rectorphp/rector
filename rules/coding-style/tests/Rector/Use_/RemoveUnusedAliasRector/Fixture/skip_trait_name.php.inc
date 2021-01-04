<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\StandaloneTrait as DuplicatedStandaloneTrait;

trait StandaloneTrait
{
    use DuplicatedStandaloneTrait;
}
