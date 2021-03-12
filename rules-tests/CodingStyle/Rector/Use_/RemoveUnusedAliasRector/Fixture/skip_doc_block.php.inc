<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\Mapping as ORM;

class SkipDocBlock
{
    /**
     * @ORM\Id
     */
    protected $id;
}
