<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\Mapping as ORM;

class SkipDocBlock
{
    /**
     * @ORM\Id
     */
    protected $id;
}
