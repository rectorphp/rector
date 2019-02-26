<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\Mapping as ORM;

class DocBlock
{
    /**
     * @ORM\Id
     * @ORM\Pod
     */
    protected $id;
}
