<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @ORM\Table("Table_Name")
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 */
class ShouldKeepAllDocBlocksAnnotationsParameters
{
    /**
     * @Route(
     *     "/{arg1}/{arg2}",
     *     defaults={"arg1"=null, "arg2"=""},
     *     requirements={"arg1"="\d+", "arg2"=".*"}
     * )
     */
    public function nothing(): void
    {
    }
}
