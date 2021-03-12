<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("Table_Name")
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 */
class SkipShouldKeepAllDocBlocksAnnotationsParameters
{
    public function nothing(): void
    {
    }
}
